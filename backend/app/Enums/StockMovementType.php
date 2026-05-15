<?php

namespace App\Enums;

/**
 * Direção da movimentação de estoque.
 */
enum StockMovementType: string
{
    case IN = 'IN';
    case OUT = 'OUT';
}
