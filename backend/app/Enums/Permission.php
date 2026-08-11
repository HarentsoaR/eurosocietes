<?php

namespace App\Enums;

/**
 * Platform permissions (RBAC). Values are stored in the database `permissions.name` column.
 *
 * Naming convention: `{resource}.{action}`.
 * This is the single source of truth for permission names.
 */
enum Permission: string
{
    // Users
    case UserView = 'users.view';
    case UserCreate = 'users.create';
    case UserUpdate = 'users.update';
    case UserDelete = 'users.delete';

    // Companies / establishments
    case CompanyView = 'companies.view';
    case CompanyCreate = 'companies.create';
    case CompanyUpdate = 'companies.update';
    case CompanyDelete = 'companies.delete';

    // Content (editorial blocks, FAQ, AI content)
    case ContentView = 'content.view';
    case ContentCreate = 'content.create';
    case ContentUpdate = 'content.update';
    case ContentDelete = 'content.delete';
    case ContentPublish = 'content.publish';

    // Company-owned profile management (self-service)
    case ProfileView = 'profile.view';
    case ProfileUpdate = 'profile.update';

    /**
     * All permissions granted to a role. Returns the role's full permission set.
     *
     * @return list<Permission>
     */
    public static function forRole(Role $role): array
    {
        return match ($role) {
            Role::Admin => self::cases(),

            Role::Editor => [
                self::CompanyView,
                self::CompanyUpdate,
                self::ContentView,
                self::ContentCreate,
                self::ContentUpdate,
                self::ContentDelete,
                self::ContentPublish,
            ],

            Role::Company => [
                self::CompanyView,
                self::ContentView,
                self::ProfileView,
                self::ProfileUpdate,
            ],

            Role::User => [
                self::CompanyView,
                self::ContentView,
            ],
        };
    }
}
