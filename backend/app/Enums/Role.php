<?php

namespace App\Enums;

/**
 * Platform roles (RBAC). Values are stored in the database `roles.name` column.
 */
enum Role: string
{
    case Admin = 'admin';
    case Editor = 'editeur';
    case Company = 'entreprise';
    case User = 'utilisateur';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Admin',
            self::Editor => 'Éditeur',
            self::Company => 'Entreprise',
            self::User => 'Utilisateur',
        };
    }
}
