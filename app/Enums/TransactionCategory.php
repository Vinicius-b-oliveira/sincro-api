<?php

namespace App\Enums;

enum TransactionCategory: string
{
    // Income
    case RENT_RECEIVED = 'Aluguel Recebido';
    case BONUS = 'Bônus';
    case CASHBACK = 'Cashback';
    case DIVIDENDS = 'Dividendos';
    case FREELANCE = 'Freelance';
    case INVESTMENTS = 'Investimentos';
    case GIFT = 'Presente';
    case REFUND = 'Reembolso';
    case SALARY = 'Salário';
    case SALES = 'Vendas';

    // Expense
    case FOOD = 'Alimentação';
    case SUBSCRIPTIONS = 'Assinaturas';
    case BEAUTY = 'Beleza';
    case HOME = 'Casa';
    case SHOPPING = 'Compras';
    case BILLS = 'Contas';
    case DEBTS = 'Dívidas';
    case DONATIONS = 'Doações';
    case EDUCATION = 'Educação';
    case ELECTRONICS = 'Eletrônicos';
    case TAXES = 'Impostos';
    case LEISURE = 'Lazer';
    case MARKET = 'Mercado';
    case HOUSING = 'Moradia';
    case PETS = 'Pets';
    case HEALTH = 'Saúde';
    case SERVICES = 'Serviços';
    case TRANSPORT = 'Transporte';
    case CLOTHING = 'Vestuário';
    case TRAVEL = 'Viagem';

    // Common
    case OTHERS = 'Outros';

    public static function income(): array
    {
        return [
            self::RENT_RECEIVED,
            self::BONUS,
            self::CASHBACK,
            self::DIVIDENDS,
            self::FREELANCE,
            self::INVESTMENTS,
            self::GIFT,
            self::REFUND,
            self::SALARY,
            self::SALES,
            self::OTHERS,
        ];
    }

    public static function expense(): array
    {
        return [
            self::FOOD,
            self::SUBSCRIPTIONS,
            self::BEAUTY,
            self::HOME,
            self::SHOPPING,
            self::BILLS,
            self::DEBTS,
            self::DONATIONS,
            self::EDUCATION,
            self::ELECTRONICS,
            self::TAXES,
            self::LEISURE,
            self::MARKET,
            self::HOUSING,
            self::PETS,
            self::HEALTH,
            self::SERVICES,
            self::TRANSPORT,
            self::CLOTHING,
            self::TRAVEL,
            self::OTHERS,
        ];
    }
}
