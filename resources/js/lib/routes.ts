/**
 * Simple route helper for Inertia
 * In production, you might want to use Ziggy or generate routes from Laravel
 */

export function route(name: string, params?: any): string {
    // Map route names to URLs
    const routes: Record<string, string | ((params?: any) => string)> = {
        // Dashboard
        'dashboard': '/',
        
        // Bookings
        'bookings.index': '/bookings',
        'bookings.create': '/bookings/create',
        'bookings.show': (id: number) => `/bookings/${id}`,
        'bookings.update': (id: number) => `/bookings/${id}`,
        'bookings.destroy': (id: number) => `/bookings/${id}`,
        
        // Services
        'services.index': '/services',
        'services.create': '/services/create',
        'services.show': (id: number) => `/services/${id}`,
        'services.update': (id: number) => `/services/${id}`,
        'services.destroy': (id: number) => `/services/${id}`,
        
        // Staff
        'staff.index': '/staff',
        'staff.show': (id: number) => `/staff/${id}`,
        
        // Customers
        'customers.index': '/customers',
        'customers.show': (id: number) => `/customers/${id}`,
        
        // Central
        'central.dashboard': '/central/dashboard',
        'central.tenants.index': '/central/tenants',
        'central.tenants.create': '/central/tenants/create',
        'central.tenants.show': (id: string) => `/central/tenants/${id}`,
        'central.tenants.store': '/central/tenants',
        'central.tenants.update': (id: string) => `/central/tenants/${id}`,
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
