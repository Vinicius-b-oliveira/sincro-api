# Endpoint de Balance - Documentação

## Visão Geral

O endpoint `/api/v1/balance` foi implementado para fornecer dados para os cards de resumo da Home e do Topo dos Grupos da aplicação Sincro.

## Funcionalidades

### 1. Balance Pessoal (Home)

**Requisição:** `GET /api/v1/balance`

Retorna o saldo pessoal do usuário logado (transações sem grupo):

-   `total_balance`: Saldo acumulado de todas as transações pessoais
-   `period_income`: Receitas do mês atual
-   `period_expenses`: Despesas do mês atual

### 2. Balance do Grupo

**Requisição:** `GET /api/v1/balance?group_id=1`

Retorna o saldo de um grupo específico:

-   `total_balance`: Saldo acumulado de todas as transações do grupo
-   `period_income`: Receitas do grupo no mês atual
-   `period_expenses`: Despesas do grupo no mês atual

## Contrato da API

### Response Schema

```json
{
    "total_balance": 1250.5,
    "period_income": 3000.0,
    "period_expenses": 1749.5
}
```

### Campos

| Campo             | Tipo  | Descrição                                   |
| ----------------- | ----- | ------------------------------------------- |
| `total_balance`   | float | Saldo total acumulado (receitas - despesas) |
| `period_income`   | float | Total de receitas no mês atual              |
| `period_expenses` | float | Total de despesas no mês atual              |

## Exemplos de Uso

### Balance Pessoal

```bash
curl -X GET "http://localhost/api/v1/balance" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

**Response:**

```json
{
    "total_balance": 2750.0,
    "period_income": 5000.0,
    "period_expenses": 2250.0
}
```

### Balance de Grupo

```bash
curl -X GET "http://localhost/api/v1/balance?group_id=1" \
  -H "Authorization: Bearer YOUR_ACCESS_TOKEN" \
  -H "Accept: application/json"
```

**Response:**

```json
{
    "total_balance": 1500.5,
    "period_income": 3000.0,
    "period_expenses": 1499.5
}
```

## Autorização

-   **Autenticação:** Requerida (Bearer Token)
-   **Acesso a Grupos:** O usuário deve ser membro do grupo para acessar seu balance

## Códigos de Resposta

| Código | Descrição                        |
| ------ | -------------------------------- |
| 200    | Sucesso                          |
| 401    | Não autenticado                  |
| 403    | Não autorizado a acessar o grupo |
| 404    | Grupo não encontrado             |

## Implementação

O endpoint foi implementado em:

-   **Controller:** `App\Http\Controllers\Api\V1\AnalyticsController::balance()`
-   **Rota:** `GET /api/v1/balance`
-   **Middlewares:** `auth:sanctum`
-   **Policies:** `GroupPolicy::view()` (para grupos)

### Lógica de Negócio

1. **Sem group_id:** Retorna apenas transações pessoais (`group_id = null`)
2. **Com group_id:** Retorna transações do grupo especificado
3. **Total Balance:** Soma todas as receitas e subtrai todas as despesas (histórico completo)
4. **Period Income/Expenses:** Considera apenas transações do mês atual

## Testes

Os testes estão implementados em `tests/Feature/BalanceTest.php` e cobrem:

-   ✅ Balance pessoal
-   ✅ Balance de grupo
-   ✅ Acesso não autorizado
-   ✅ Grupo não autorizado

Para executar os testes:

```bash
./vendor/bin/sail artisan test tests/Feature/BalanceTest.php
```
