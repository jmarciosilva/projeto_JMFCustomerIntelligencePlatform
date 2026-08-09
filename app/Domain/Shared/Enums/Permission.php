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
    case AffiliateProgramsView = 'affiliate_programs.view';
    case AffiliateProgramsCreate = 'affiliate_programs.create';
    case AffiliateProgramsUpdate = 'affiliate_programs.update';
    case AffiliateProgramsDelete = 'affiliate_programs.delete';
    case AffiliateProductsView = 'affiliate_products.view';
    case AffiliateProductsCreate = 'affiliate_products.create';
    case AffiliateProductsUpdate = 'affiliate_products.update';
    case AffiliateProductsDelete = 'affiliate_products.delete';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $permission) => $permission->value, self::cases());
    }
}
