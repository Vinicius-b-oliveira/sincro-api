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
    /**
     * List transactions
     *
     * @group Transactions
     *
     * @authenticated
     *
     * @description Returns a paginated list of transactions.
     * By default, it returns the authenticated user's personal transactions.
     * If the `group_id` query parameter is provided, it returns all transactions from all members of that group.
     *
     * @queryParam group_id integer An optional ID of a group to filter transactions by. Example: 1
     *
     * @responseFromApiResource App\Http\Resources\V1\Transaction\TransactionResource
     */
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

    /**
     * Create a new transaction
     *
     * @group Transactions
     *
     * @authenticated
     *
     * @responseFromApiResource App\Http\Resources\V1\Transaction\TransactionResource status=201
     */
    public function store(StoreTransactionRequest $request)
    {
        $this->authorize('create', Transaction::class);

        $transaction = $request->user()->transactions()->create($request->validated());

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Get a specific transaction
     *
     * @group Transactions
     *
     * @authenticated
     *
     * @urlParam transaction integer required The ID of the transaction. Example: 1
     *
     * @responseFromApiResource App\Http\Resources\V1\Transaction\TransactionResource
     */
    public function show(Transaction $transaction)
    {
        $this->authorize('view', $transaction);

        return new TransactionResource($transaction);
    }

    /**
     * Update a transaction
     *
     * @group Transactions
     *
     * @authenticated
     *
     * @urlParam transaction integer required The ID of the transaction. Example: 1
     *
     * @responseFromApiResource App\Http\Resources\V1\Transaction\TransactionResource
     */
    public function update(UpdateTransactionRequest $request, Transaction $transaction)
    {
        $this->authorize('update', $transaction);

        $transaction->update($request->validated());

        return new TransactionResource($transaction);
    }

    /**
     * Delete a transaction
     *
     * @group Transactions
     *
     * @authenticated
     *
     * @urlParam transaction integer required The ID of the transaction. Example: 1
     *
     * @response 204
     */
    public function destroy(Transaction $transaction)
    {
        $this->authorize('delete', $transaction);

        $transaction->delete();

        return response()->noContent();
    }
}
