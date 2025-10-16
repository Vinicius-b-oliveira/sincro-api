<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Group\StoreGroupRequest;
use App\Http\Requests\Api\V1\Group\UpdateGroupRequest;
use App\Http\Requests\Api\V1\Group\UpdateMemberRoleRequest;
use App\Http\Resources\V1\Group\GroupResource;
use App\Http\Resources\V1\Member\MemberResource;
use App\Models\Group;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class GroupController extends Controller
{

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

    public function show(Group $group)
    {
        $this->authorize('view', $group);

        return new GroupResource($group->load('owner'));
    }

    public function update(UpdateGroupRequest $request, Group $group)
    {
        $this->authorize('update', $group);

        $group->update($request->validated());

        return new GroupResource($group);
    }

    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $group->delete();

        return response()->noContent();
    }

    public function removeMember(Group $group, User $user)
    {
        $this->authorize('removeMember', [$group, $user]);

        $group->members()->detach($user->id);

        return response()->noContent();
    }

    public function listMembers(Group $group)
    {
        $this->authorize('view', $group);

        $members = $group->members()->paginate();

        return MemberResource::collection($members);
    }

    public function updateMemberRole(UpdateMemberRoleRequest $request, Group $group, User $user)
    {
        $this->authorize('updateMemberRole', [$group, $user]);

        $group->members()->updateExistingPivot($user->id, [
            'role' => $request->validated('role'),
        ]);

        return response()->noContent();
    }
}
