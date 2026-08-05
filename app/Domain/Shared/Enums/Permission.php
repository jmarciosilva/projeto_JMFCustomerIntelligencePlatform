<?php

namespace App\Domain\Shared\Enums;

enum Permission: string
{
    case UsersView = 'users.view';
    case UsersCreate = 'users.create';
    case UsersUpdate = 'users.update';
    case UsersDelete = 'users.delete';
    case AuditView = 'audit.view';
    case TenantsView = 'tenants.view';
    case TenantsCreate = 'tenants.create';
    case TenantsUpdate = 'tenants.update';
    case TenantsDelete = 'tenants.delete';
    case ApplicationsView = 'applications.view';
    case ApplicationsCreate = 'applications.create';
    case ApplicationsUpdate = 'applications.update';
    case ApplicationsDelete = 'applications.delete';
    case ApplicationsTokensManage = 'applications.tokens.manage';
    case ContactsView = 'contacts.view';
    case AnalyticsView = 'analytics.view';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission) => $permission->value, self::cases());
    }
}
