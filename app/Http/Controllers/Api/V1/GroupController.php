<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Group\StoreGroupRequest;
use App\Http\Requests\Api\V1\Group\UpdateGroupRequest;
use App\Http\Requests\Api\V1\Group\UpdateMemberRoleRequest;
use App\Http\Resources\V1\Group\GroupResource;
use App\Http\Resources\V1\Member\MemberResource;
use App\Models\Group;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Throwable;

class GroupController extends Controller
{
    /**
     * List my groups
     *
     * @group Group Management
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\Group\GroupResource
     */
    public function index(Request $request)
    {
        $groups = $request->user()->groups()
            ->with('owner')
            ->withCount('members')
            ->latest()
            ->paginate();

        return GroupResource::collection($groups);
    }

    /**
     * Create a new group
     *
     * @group Group Management
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\Group\GroupResource status=201
     *
     * @throws Throwable
     */
    public function store(StoreGroupRequest $request)
    {
        $this->authorize('create', Group::class);

        $validated = $request->validated();
        $user = $request->user();

        $group = DB::transaction(function () use ($validated, $user) {
            $group = $user->ownedGroups()->create($validated);
            $group->members()->attach($user->id, ['role' => 'admin']);

            return $group;
        });

        return (new GroupResource($group->load('owner')))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Get a specific group's details
     *
     * @group Group Management
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\Group\GroupResource
     */
    public function show(Group $group)
    {
        $this->authorize('view', $group);

        return new GroupResource($group->load('owner'));
    }

    /**
     * Update a group's details
     *
     * @group Group Management
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\Group\GroupResource
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $this->authorize('update', $group);
        $group->update($request->validated());

        return new GroupResource($group);
    }

    /**
     * Delete a group
     *
     * @group Group Management
     *
     * @authenticated
     *
     * @response 204
     */
    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);
        $group->delete();

        return response()->noContent();
    }

    /**
     * List group members
     *
     * @group Member Management
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\Member\MemberResource
     */
    public function listMembers(Group $group)
    {
        $this->authorize('view', $group);
        $members = $group->members()->paginate();

        return MemberResource::collection($members);
    }

    /**
     * Update a member's role
     *
     * @group Member Management
     *
     * @authenticated
     *
     * @response 204
     */
    public function updateMemberRole(UpdateMemberRoleRequest $request, Group $group, User $user)
    {
        $this->authorize('updateMemberRole', [$group, $user]);
        $group->members()->updateExistingPivot($user->id, [
            'role' => $request->validated('role'),
        ]);

        return response()->noContent();
    }

    /**
     * Remove a member from a group
     *
     * @group Member Management
     *
     * @authenticated
     *
     * @response 204
     */
    public function removeMember(Group $group, User $user)
    {
        $this->authorize('removeMember', [$group, $user]);
        $group->members()->detach($user->id);

        return response()->noContent();
    }

    /**
     * Export group transactions as CSV
     *
     * @group Group Management
     * @authenticated
     *
     * @response 200 {file} CSV Exported file
     */
    public function export(Request $request, Group $group): StreamedResponse
    {
        $this->authorize('export', $group);

        $fileName = 'export_grupo_' . $group->id . '_' . now()->format('Ymd_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $fileName . '"',
        ];

        return new StreamedResponse(function () use ($group) {
            $handle = fopen('php://output', 'w');
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($handle, [
                'Data',
                'Tipo',
                'Categoria',
                'Titulo',
                'Membro (Email)',
                'Valor (R$)',
            ]);

            $group->transactions()
                ->with('user')
                ->chunk(200, function ($transactions) use ($handle) {
                    foreach ($transactions as $tx) {
                        fputcsv($handle, [
                            $tx->transaction_date->toIso8601String(),
                            $tx->type->value,
                            $tx->category,
                            $tx->title,
                            $tx->user->email ?? 'N/A',
                            number_format($tx->amount, 2, ',', '.'),
                        ]);
                    }
                });

            fclose($handle);
        }, Response::HTTP_OK, $headers);
    }

    /**
     * Clear all group transaction history
     *
     * @group Group Management
     * @authenticated
     *
     * @response 200 { "message": "Histórico limpo com sucesso.", "transactions_deleted": 15 }
     */
    public function clearHistory(Request $request, Group $group)
    {
        $this->authorize('clearHistory', $group);

        $deletedCount = Transaction::where('group_id', $group->id)->delete();

        return response()->json([
            'message' => 'Histórico limpo com sucesso.',
            'transactions_deleted' => $deletedCount,
        ]);
    }

}
