<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Group\StoreGroupRequest;
use App\Http\Requests\Api\V1\Group\UpdateGroupRequest;
use App\Http\Resources\V1\Group\GroupResource;
use App\Models\Group;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class GroupController extends Controller
{
    /**
     * Exibe uma lista dos grupos aos quais o usuário pertence.
     */
    public function index(Request $request)
    {
        $groups = $request->user()->groups()->latest()->paginate();

        return GroupResource::collection($groups);
    }

    /**
     * Armazena um novo grupo.
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
     * Exibe um grupo específico.
     */
    public function show(Group $group)
    {
        $this->authorize('view', $group);

        return new GroupResource($group->load('owner'));
    }

    /**
     * Atualiza um grupo específico.
     */
    public function update(UpdateGroupRequest $request, Group $group)
    {
        $this->authorize('update', $group);

        $group->update($request->validated());

        return new GroupResource($group);
    }

    /**
     * Remove um grupo.
     */
    public function destroy(Group $group)
    {
        $this->authorize('delete', $group);

        $group->delete();

        return response()->noContent();
    }
}
