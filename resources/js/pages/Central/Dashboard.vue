<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Building2Icon, UsersIcon, DollarSignIcon, CalendarIcon, Globe, LayoutDashboard } from 'lucide-vue-next';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';

interface Stats {
    total_tenants: number;
    active_tenants: number;
    trial_tenants: number;
    suspended_tenants: number;
    total_plans: number;
    active_plans: number;
    total_subscriptions: number;
    active_subscriptions: number;
}

interface Tenant {
    id: string;
    name: string;
    email: string;
    status: string;
    plan?: {
        name: string;
    };
}

interface Subscription {
    id: number;
    status: string;
    tenant?: {
        name: string;
    };
    plan?: {
        name: string;
    };
}

interface Props {
    stats: Stats;
    recentTenants: Tenant[];
    recentSubscriptions: Subscription[];
}

const props = defineProps<Props>();

const goToTenantPanel = (tenantId: string) => {
    router.visit(`/set-tenant/${tenantId}`);
};
</script>

<template>
    <Head title="Central Dashboard" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Central Dashboard</h1>
                <p class="text-muted-foreground">
                    Обзор платформы
                </p>
            </div>

            <!-- Stats -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Всего Tenants</CardTitle>
                        <Building2Icon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_tenants }}</div>
                        <p class="text-xs text-muted-foreground">
                            Активных: {{ stats.active_tenants }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Тарифные планы</CardTitle>
                        <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_plans }}</div>
                        <p class="text-xs text-muted-foreground">
                            Активных: {{ stats.active_plans }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Подписки</CardTitle>
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_subscriptions }}</div>
                        <p class="text-xs text-muted-foreground">
                            Активных: {{ stats.active_subscriptions }}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Пробный период</CardTitle>
                        <UsersIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.trial_tenants }}</div>
                        <p class="text-xs text-muted-foreground">
                            На пробном периоде
                        </p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Recent Tenants -->
                <Card>
                    <CardHeader>
                        <CardTitle>Последние Tenants</CardTitle>
                        <CardDescription>Недавно созданные tenants</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="tenant in recentTenants"
                                :key="tenant.id"
                                class="flex items-center justify-between"
                            >
                                <div>
                                    <p class="font-medium">{{ tenant.name }}</p>
                                    <p class="text-sm text-muted-foreground">{{ tenant.email }}</p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <Badge :variant="tenant.status === 'active' ? 'default' : 'secondary'">
                                        {{ tenant.status }}
                                    </Badge>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8"
                                                    :as-child="true"
                                                >
                                                    <a
                                                        :href="`/${tenant.id}`"
                                                        target="_blank"
                                                        rel="noopener noreferrer"
                                                    >
                                                        <Globe class="h-4 w-4" />
                                                    </a>
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Открыть сайт</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                    <TooltipProvider>
                                        <Tooltip>
                                            <TooltipTrigger as-child>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    class="h-8 w-8"
                                                    @click="goToTenantPanel(tenant.id)"
                                                >
                                                    <LayoutDashboard class="h-4 w-4" />
                                                </Button>
                                            </TooltipTrigger>
                                            <TooltipContent>
                                                <p>Перейти в панель</p>
                                            </TooltipContent>
                                        </Tooltip>
                                    </TooltipProvider>
                                </div>
                            </div>
                            <p v-if="recentTenants.length === 0" class="text-muted-foreground text-sm">
                                Нет tenants
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recent Subscriptions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Последние подписки</CardTitle>
                        <CardDescription>Недавно созданные подписки</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="subscription in recentSubscriptions"
                                :key="subscription.id"
                                class="flex items-center justify-between"
                            >
                                <div>
                                    <p class="font-medium">{{ subscription.tenant?.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ subscription.plan?.name }}
                                    </p>
                                </div>
                                <Badge :variant="subscription.status === 'active' ? 'default' : 'secondary'">
                                    {{ subscription.status }}
                                </Badge>
                            </div>
                            <p v-if="recentSubscriptions.length === 0" class="text-muted-foreground text-sm">
                                Нет подписок
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
