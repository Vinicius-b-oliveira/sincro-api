<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Transaction\ListTransactionRequest;
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
     * List transactions (Refatorado para filtros)
     *
     * @group Transactions
     * @authenticated
     * @description Retorna uma lista paginada de transações do usuário autenticado,
     * com suporte a filtros avançados.
     *
     * @queryParam search string Texto para busca no título. Example: "Supermercado"
     * @queryParam type string 'income' ou 'expense'. Example: "expense"
     * @queryParam category[] array Categorias para filtrar. Example: ["Alimentação", "Transporte"]
     * @queryParam group_id[] array IDs de grupo para filtrar. Example: [1, 5]
     * @queryParam date_start string Data de início (ISO 8601). Example: "2025-01-01"
     * @queryParam date_end string Data de fim (ISO 8601). Example: "2025-01-31"
     *
     * @responseFromApiResource App\Http\Resources\V1\Transaction\TransactionResource
     */
    public function index(ListTransactionRequest $request)
    {
        $filters = $request->validated();

        $query = $request->user()->transactions();

        $query->when($filters['search'] ?? null, function ($q, $search) {
            $q->where('title', 'like', "%{$search}%");
        });

        $query->when($filters['type'] ?? null, function ($q, $type) {
            $q->where('type', $type);
        });

        $query->when($filters['category'] ?? null, function ($q, $categories) {
            $q->whereIn('category', $categories);
        });

        $query->when($filters['group_id'] ?? null, function ($q, $groupIds) {
            $q->whereIn('group_id', $groupIds);
        });

        $query->when($filters['date_start'] ?? null, function ($q, $dateStart) {
            $q->where('transaction_date', '>=', $dateStart);
        });

        $query->when($filters['date_end'] ?? null, function ($q, $dateEnd) {
            $q->where('transaction_date', '<=', $dateEnd);
        });

        $transactions = $query->latest('transaction_date')->paginate();

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
        $validated = $request->validated();

        $this->authorize('create', [Transaction::class, $validated['group_id'] ?? null]);

        $transaction = $request->user()->transactions()->create($validated);

        return (new TransactionResource($transaction))
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * @group Transactions
     * @authenticated
     * @description Retorna as transações de TODOS os membros de um grupo específico.
     *
     * @urlParam group integer required O ID do grupo.
     *
     * @responseFromApiResource App\Http\Resources\V1\Transaction\TransactionResource
     */
    public function listGroupTransactions(Request $request, Group $group)
    {
        $this->authorize('viewGroupTransactions', $group);

        $transactions = Transaction::where('group_id', $group->id)
            ->latest('transaction_date')
            ->paginate();

        return TransactionResource::collection($transactions);
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
