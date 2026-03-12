/**
 * Simple route helper for Inertia
 * Tenant panel: /dashboard, /bookings, /services и т.д. (tenant из сессии)
 */

export function route(name: string, params?: any): string {
    const routes: Record<string, string | ((params?: any) => string)> = {
        // Dashboard (tenant panel)
        'dashboard': '/dashboard',
        'tenant.landing': (id: string) => `/${id}`,
        'setTenant': (id: string) => `/set-tenant/${id}`,
        
        // Bookings (tenant)
        'bookings.index': '/bookings',
        'bookings.create': '/bookings/create',
        'bookings.store': '/bookings',
        'bookings.show': (id: number) => `/bookings/${id}`,
        'bookings.edit': (id: number) => `/bookings/${id}/edit`,
        'bookings.update': (id: number) => `/bookings/${id}`,
        'bookings.destroy': (id: number) => `/bookings/${id}`,
        
        // Services (tenant)
        'services.index': '/services',
        'services.store': '/services',
        'services.show': (id: number) => `/services/${id}`,
        'services.edit': (id: number) => `/services/${id}/edit`,
        'services.update': (id: number) => `/services/${id}`,
        'services.destroy': (id: number) => `/services/${id}`,
        
        // Staff (tenant)
        'staff.index': '/staff',
        'staff.create': '/staff/create',
        'staff.store': '/staff',
        'staff.show': (id: number) => `/staff/${id}`,
        'staff.edit': (id: number) => `/staff/${id}/edit`,
        'staff.update': (id: number) => `/staff/${id}`,
        'staff.destroy': (id: number) => `/staff/${id}`,
        
        // Customers (tenant)
        'customers.index': '/customers',
        'customers.create': '/customers/create',
        'customers.store': '/customers',
        'customers.show': (id: number) => `/customers/${id}`,
        'customers.edit': (id: number) => `/customers/${id}/edit`,
        'customers.update': (id: number) => `/customers/${id}`,
        'customers.destroy': (id: number) => `/customers/${id}`,
        
        // Reports (tenant)
        'reports.index': '/reports',
        
        // Business (tenant) - точки продаж
        'business.index': '/business',
        'business.create': '/business/create',
        'business.store': '/business',
        'business.show': (id: number) => `/business/${id}`,
        'business.edit': (id: number) => `/business/${id}/edit`,
        'business.update': (id: number) => `/business/${id}`,
        'business.destroy': (id: number) => `/business/${id}`,
        
        // Settings (tenant) - настройки компании
        'settings.index': '/settings',
        'settings.update': '/settings',
        
        // Roles (tenant)
        'roles.index': '/roles',
        'roles.create': '/roles/create',
        'roles.store': '/roles',
        'roles.show': (id: number) => `/roles/${id}`,
        'roles.edit': (id: number) => `/roles/${id}/edit`,
        'roles.update': (id: number) => `/roles/${id}`,
        'roles.destroy': (id: number) => `/roles/${id}`,
        
        // Central
        'central.dashboard': '/central/dashboard',
        'central.tenants.index': '/central/tenants',
        'central.tenants.create': '/central/tenants/create',
        'central.tenants.show': (id: string) => `/central/tenants/${id}`,
        'central.tenants.edit': (id: string) => `/central/tenants/${id}/edit`,
        'central.tenants.store': '/central/tenants',
        'central.tenants.update': (id: string) => `/central/tenants/${id}`,
        'central.tenants.destroy': (id: string) => `/central/tenants/${id}`,
        'central.tenants.attachUser': (id: string) => `/central/tenants/${id}/attach-user`,
        'central.tenants.createDatabase': (id: string) => `/central/tenants/${id}/create-database`,
        'central.tenants.updateDatabase': (id: string) => `/central/tenants/${id}/update-database`,
        'central.plans.index': '/central/plans',
        'central.plans.create': '/central/plans/create',
        'central.plans.show': (id: number) => `/central/plans/${id}`,
        'central.plans.edit': (id: number) => `/central/plans/${id}/edit`,
        'central.plans.store': '/central/plans',
        'central.plans.update': (id: number) => `/central/plans/${id}`,
        'central.plans.destroy': (id: number) => `/central/plans/${id}`,
        'central.subscriptions.index': '/central/subscriptions',
        'central.subscriptions.show': (id: number) => `/central/subscriptions/${id}`,
        'central.languages.index': '/central/languages',
        'central.languages.create': '/central/languages/create',
        'central.languages.show': (id: number) => `/central/languages/${id}`,
        'central.languages.edit': (id: number) => `/central/languages/${id}/edit`,
        'central.languages.store': '/central/languages',
        'central.languages.update': (id: number) => `/central/languages/${id}`,
        'central.languages.destroy': (id: number) => `/central/languages/${id}`,
        'central.languages.translations.update': (id: number) => `/central/languages/${id}/translations`,
        'central.settings.index': '/central/settings',
        'central.settings.general.update': '/central/settings/general',
        'central.settings.payment.update': '/central/settings/payment',
        'central.settings.email.update': '/central/settings/email',
        'central.settings.whatsapp.update': '/central/settings/whatsapp',
        'central.users.index': '/central/users',
        'central.users.create': '/central/users/create',
        'central.users.show': (id: number) => `/central/users/${id}`,
        'central.users.edit': (id: number) => `/central/users/${id}/edit`,
        'central.users.store': '/central/users',
        'central.users.update': (id: number) => `/central/users/${id}`,
        'central.users.destroy': (id: number) => `/central/users/${id}`,
    };

    const route = routes[name];
    
    if (!route) {
        console.warn(`Route "${name}" not found`);
        return '#';
    }

    if (typeof route === 'function') {
        return route(params);
    }

    return route;
}
