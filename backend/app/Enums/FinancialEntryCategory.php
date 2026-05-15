<?php

namespace App\Enums;

/**
 * Categoria de um lançamento financeiro.
 */
enum FinancialEntryCategory: string
{
    case SALE = 'SALE';
    case PURCHASE = 'PURCHASE';
    case OTHER = 'OTHER';
}
