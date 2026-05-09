<?php

namespace App\Enums;

/**
 * Enum para os perfis de usuario do sistema (RBAC).
 */
enum Role: string
{
    case ADMIN = 'admin';
    case MANAGER = 'manager';
    case SELLER = 'seller';
    case VIEWER = 'viewer';
}
