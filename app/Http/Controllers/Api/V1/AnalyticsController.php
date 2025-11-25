<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Analytics\GetSummaryRequest;
use App\Http\Resources\V1\Analytics\SummaryResource;
use App\Models\Group;
use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    /**
     * Get analytics summary
     *
     * @group Analytics
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\Analytics\SummaryResource
     */
    public function summary(GetSummaryRequest $request)
    {
        $filters = $request->validated();
        $user = $request->user();

        $query = $this->buildAnalyticsQuery($filters, $user);

        [$dateStart, $dateEnd] = $this->resolveDateRange($filters);
        $query->whereBetween('transaction_date', [$dateStart, $dateEnd]);

        $chartData = (clone $query)
            ->where('type', 'expense')
            ->select('category', DB::raw('SUM(amount) as total'))
            ->groupBy('category')
            ->orderBy('total', 'desc')
            ->get()
            ->map(function ($item) {
                $item->total = (float) $item->total;
                return $item;
            });

        $statsQuery = (clone $query)->where('type', 'expense');

        $totalSpent = $statsQuery->sum('amount');
        $monthlyAverage = $this->calculateMonthlyAverage($statsQuery);
        $maxSpent = $statsQuery->max('amount');
        $minSpent = $statsQuery->min('amount');

        $summaryStats = [
            'total_spent' => (float) $totalSpent,
            'monthly_average' => (float) $monthlyAverage,
            'max_spent' => (float) ($maxSpent ?? 0),
            'min_spent' => (float) ($minSpent ?? 0),
        ];

        return new SummaryResource([
            'chart_data' => $chartData,
            'summary_stats' => $summaryStats,
            'filters' => [
                'date_start' => $dateStart->toIso8601String(),
                'date_end' => $dateEnd->toIso8601String(),
            ]
        ]);
    }

    private function buildAnalyticsQuery(array $filters, $user)
    {
        $groupId = $filters['group_id'] ?? null;
        $viewMode = $filters['view_mode'] ?? 'individual';

        if ($groupId) {
            $group = Group::findOrFail($groupId);
            $this->authorize('view', $group);

            if ($viewMode === 'individual') {
                return Transaction::where('group_id', $group->id)
                    ->where('user_id', $user->id);
            }

            return Transaction::where('group_id', $group->id);
        }

        return $user->transactions()->whereNull('group_id');
    }

    private function resolveDateRange(array $filters): array
    {
        $now = Carbon::now();

        if (!empty($filters['period'])) {
            $dateEnd = $now->copy()->endOfDay();
            $dateStart = $now->copy()->subMonths(
                match ($filters['period']) {
                    '3m' => 3,
                    '6m' => 6,
                    '1y' => 12,
                }
            )->startOfDay();

            return [$dateStart, $dateEnd];
        }

        $dateStart = Carbon::parse($filters['start_date'])->startOfDay();
        $dateEnd = Carbon::parse($filters['end_date'])->endOfDay();

        return [$dateStart, $dateEnd];
    }

    private function calculateMonthlyAverage($query)
    {
        $dates = $query->selectRaw('MIN(transaction_date) as min_date, MAX(transaction_date) as max_date')->first();

        if (!$dates->min_date || !$dates->max_date) {
            return 0;
        }

        $min = Carbon::parse($dates->min_date);
        $max = Carbon::parse($dates->max_date);

        $months = $min->diffInMonths($max) + 1;
        $total = $query->sum('amount');

        return $total / $months;
    }
}
