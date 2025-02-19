<?php

namespace App\Enum;

enum UsersTypes: string
{
    case USER = 'ROLE_USER';
    case AGENT = 'ROLE_AGENT';
    case ADMIN = 'ROLE_ADMIN';
    case SUPER_AGENT = 'ROLE_SUPER_AGENT';

    public function getRoles(): array {
        // Define roles as an array
        $roles = match ($this) {
            self::USER => ['ROLE_USER'],
            self::AGENT => ['ROLE_AGENT'],
            self::ADMIN => ['ROLE_ADMIN'],
            self::SUPER_AGENT => ['ROLE_SUPER_AGENT'],
        };
        return $roles;
    }
}