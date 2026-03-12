<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { 
    BookOpen, 
    Folder, 
    LayoutGrid, 
    Calendar, 
    Briefcase, 
    Users, 
    UserCircle,
    Building2,
    Settings,
    BarChart3
} from 'lucide-vue-next';
import { computed } from 'vue';
import NavFooter from '@/components/NavFooter.vue';
import NavMain from '@/components/NavMain.vue';
import NavUser from '@/components/NavUser.vue';
import {
    Sidebar,
    SidebarContent,
    SidebarFooter,
    SidebarHeader,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
} from '@/components/ui/sidebar';
import { type NavItem } from '@/types';
import AppLogo from './AppLogo.vue';
import { route } from '@/lib/routes';

const page = usePage();
const user = computed(() => page.props.auth.user);

// Определяем контекст: центральный или tenant
// Центральный контекст - только когда путь начинается с /central
// Даже суперадмин на домене tenant должен видеть tenant-меню
const isCentral = computed(() => {
    const url = page.url;
    return url.startsWith('/central');
});

// Tenant navigation items
const tenantNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Бронирования',
        href: route('bookings.index'),
        icon: Calendar,
    },
    {
        title: 'Услуги',
        href: route('services.index'),
        icon: Briefcase,
    },
    {
        title: 'Сотрудники',
        href: route('staff.index'),
        icon: UserCircle,
    },
    {
        title: 'Клиенты',
        href: route('customers.index'),
        icon: Users,
    },
    {
        title: 'Отчеты',
        href: route('reports.index'),
        icon: BarChart3,
    },
    {
        title: 'Точки продаж',
        href: route('business.index'),
        icon: Building2,
    },
];

// Central navigation items
const centralNavItems: NavItem[] = [
    {
        title: 'Dashboard',
        href: route('central.dashboard'),
        icon: LayoutGrid,
    },
    {
        title: 'Tenants',
        href: route('central.tenants.index'),
        icon: Building2,
    },
    {
        title: 'Пользователи',
        href: route('central.users.index'),
        icon: Users,
    },
    {
        title: 'Тарифы',
        href: route('central.plans.index'),
        icon: Briefcase,
    },
    {
        title: 'Подписки',
        href: route('central.subscriptions.index'),
        icon: Calendar,
    },
    {
        title: 'Языки',
        href: route('central.languages.index'),
        icon: BookOpen,
    },
    {
        title: 'Настройки',
        href: route('central.settings.index'),
        icon: Settings,
    },
];

const mainNavItems = computed(() => {
    return isCentral.value ? centralNavItems : tenantNavItems;
});

const footerNavItems: NavItem[] = [
    {
        title: 'Github Repo',
        href: 'https://github.com/laravel/vue-starter-kit',
        icon: Folder,
    },
    {
        title: 'Documentation',
        href: 'https://laravel.com/docs/starter-kits#vue',
        icon: BookOpen,
    },
];
</script>

<template>
    <Sidebar collapsible="icon" variant="inset">
        <SidebarHeader>
            <SidebarMenu>
                <SidebarMenuItem>
                    <SidebarMenuButton size="lg" as-child>
                        <Link :href="isCentral ? route('central.dashboard') : route('dashboard')">
                            <AppLogo />
                        </Link>
                    </SidebarMenuButton>
                </SidebarMenuItem>
            </SidebarMenu>
        </SidebarHeader>

        <SidebarContent>
            <NavMain :items="mainNavItems" />
        </SidebarContent>

        <SidebarFooter>
            <NavUser />
            <NavFooter :items="footerNavItems" />
        </SidebarFooter>
    </Sidebar>
    <slot />
</template>
