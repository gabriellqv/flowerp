<?php

namespace App\Enums;

/**
 * Tipo de lançamento financeiro.
 */
enum FinancialEntryType: string
{
    case INCOME = 'INCOME';
    case EXPENSE = 'EXPENSE';
}
