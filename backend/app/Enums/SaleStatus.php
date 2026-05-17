<?php

namespace App\Enums;

/**
 * Status possíveis de uma venda no sistema.
 */
enum SaleStatus: string
{
    case COMPLETED = 'COMPLETED';
    case CANCELLED = 'CANCELLED';
}
