<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Analytics\GetSummaryRequest;
use App\Http\Resources\V1\Analytics\SummaryResource;
use App\Models\Group;
use App\Models\Transaction;
use App\Enums\TransactionType;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
        /**
     * Get balance summary
     *
     * This endpoint serves exclusively to feed the summary cards of the Home and Group Top screens.
     * Returns the current balance, total income and expenses for either personal finances or a specific group.
     *
     * @group Analytics
     * @authenticated
     * 
     * @queryParam group_id integer optional Group ID to filter balance by specific group. When omitted, returns personal balance (transactions without group). Example: 1
     *
     * @response 200 scenario="Personal balance" {
     *   "total_balance": 1250.50,
     *   "period_income": 3000.00,
     *   "period_expenses": 1749.50
     * }
     * 
     * @response 200 scenario="Group balance" {
     *   "total_balance": 2500.75,
     *   "period_income": 5000.00,
     *   "period_expenses": 2499.25
     * }
     * 
     * @response 403 scenario="Unauthorized group access" {
     *   "message": "This action is unauthorized."
     * }
     */
    public function balance(Request $request)
    {
        $user = $request->user();
        $groupId = $request->query('group_id');
        
        // Build query for transactions
        $query = $this->buildBalanceQuery($user, $groupId);
        
        // Calculate total balance (all time)
        $totalIncomeAllTime = (clone $query)->where('type', TransactionType::INCOME)->sum('amount');
        $totalExpensesAllTime = (clone $query)->where('type', TransactionType::EXPENSE)->sum('amount');
        $totalBalance = $totalIncomeAllTime - $totalExpensesAllTime;
        
        return response()->json([
            'total_balance' => (float) $totalBalance,
            'period_income' => (float) $totalIncomeAllTime,
            'period_expenses' => (float) $totalExpensesAllTime,
        ]);
    }

    private function buildBalanceQuery($user, $groupId = null)
    {
        if ($groupId) {
            $group = Group::findOrFail($groupId);
            $this->authorize('view', $group);

            return Transaction::where('group_id', $group->id);
        }

        return $user->transactions()->whereNull('group_id');
    }

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
