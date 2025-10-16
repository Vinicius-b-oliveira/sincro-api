<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\StoreTransactionRequest;
use App\Http\Requests\Api\V1\Transaction\UpdateTransactionRequest;
use App\Http\Resources\V1\Transaction\TransactionResource;
use App\Models\Group;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'group_id' => 'nullable|integer|exists:groups,id',
        ]);

        if ($groupId = $request->query('group_id')) {
            $group = Group::findOrFail($groupId);

            $this->authorize('viewGroupTransactions', $group);

            $memberIds = $group->members()->pluck('users.id');

            $transactions = Transaction::whereIn('user_id', $memberIds)
                ->latest()
                ->paginate();
        } else {
            $transactions = $request->user()->transactions()->latest()->paginate();
        }

        return TransactionResource::collection($transactions);
    }

    public function store(StoreTransactionRequest $request)
    {
        $this->authorize('create', Transaction::class);

        $transaction = $request->user()->transactions()->create($request->validated());

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return new TransactionResource($transaction);
    }

    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->validated());

        return new TransactionResource($transaction);
    }

    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return response()->noContent();
    }
}
