<?php

namespace App\Enums;

/**
 * Motivo da movimentação de estoque.
 */
enum StockMovementReason: string
{
    case SALE = 'SALE';
    case INITIAL_STOCK = 'INITIAL_STOCK';
    case MANUAL_ADJUSTMENT = 'MANUAL_ADJUSTMENT';
}
