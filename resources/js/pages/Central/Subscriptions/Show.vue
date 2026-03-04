<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { route } from '@/lib/routes';

interface Tenant {
    id: string;
    name: string;
    email?: string;
}

interface Plan {
    id: number;
    name: string;
    description?: string;
    price: number;
    currency: string;
    interval: string;
}

interface Subscription {
    id: number;
    status: string;
    starts_at?: string;
    ends_at?: string;
    trial_ends_at?: string;
    cancelled_at?: string;
    tenant?: Tenant;
    plan?: Plan;
}

interface Props {
    subscription: Subscription;
}

const props = defineProps<Props>();

const formatDateTime = (value?: string) => {
    if (!value) return '-';
    const date = new Date(value);
    return `${date.toLocaleDateString('ru-RU')} ${date.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' })}`;
};
</script>

<template>
    <Head :title="`Подписка #${subscription.id}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Подписка #{{ subscription.id }}
                    </h1>
                    <p class="text-muted-foreground">
                        Детальная информация о подписке tenant
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('central.subscriptions.index')">
                        <Button variant="outline">
                            Назад к списку
                        </Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">ID</p>
                            <p class="font-mono text-sm">
                                {{ subscription.id }}
                            </p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Статус</p>
                            <Badge :variant="subscription.status === 'active' ? 'default' : subscription.status === 'trialing' ? 'secondary' : 'destructive'">
                                {{ subscription.status }}
                            </Badge>
                        </div>

                        <div v-if="subscription.starts_at">
                            <p class="text-sm font-medium text-muted-foreground">Начало</p>
                            <p>{{ formatDateTime(subscription.starts_at) }}</p>
                        </div>

                        <div v-if="subscription.ends_at">
                            <p class="text-sm font-medium text-muted-foreground">Окончание</p>
                            <p>{{ formatDateTime(subscription.ends_at) }}</p>
                        </div>

                        <div v-if="subscription.trial_ends_at">
                            <p class="text-sm font-medium text-muted-foreground">Окончание пробного периода</p>
                            <p>{{ formatDateTime(subscription.trial_ends_at) }}</p>
                        </div>

                        <div v-if="subscription.cancelled_at">
                            <p class="text-sm font-medium text-muted-foreground">Отменена</p>
                            <p>{{ formatDateTime(subscription.cancelled_at) }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Tenant и тариф</CardTitle>
                        <CardDescription>
                            Информация о клиенте и выбранном тарифном плане
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="subscription.tenant">
                            <p class="text-sm font-medium text-muted-foreground">Tenant</p>
                            <p class="font-medium">
                                {{ subscription.tenant.name }}
                            </p>
                            <p v-if="subscription.tenant.email" class="text-sm text-muted-foreground">
                                {{ subscription.tenant.email }}
                            </p>

                            <div class="mt-2">
                                <Link :href="route('central.tenants.show', subscription.tenant.id)">
                                    <Button size="sm" variant="outline">
                                        Открыть tenant
                                    </Button>
                                </Link>
                            </div>
                        </div>
                        <div v-else>
                            <p class="text-sm text-muted-foreground">
                                Tenant не найден
                            </p>
                        </div>

                        <div v-if="subscription.plan" class="mt-4">
                            <p class="text-sm font-medium text-muted-foreground">Тарифный план</p>
                            <p class="font-medium">
                                {{ subscription.plan.name }}
                            </p>
                            <p v-if="subscription.plan.description" class="text-sm text-muted-foreground">
                                {{ subscription.plan.description }}
                            </p>
                            <p class="mt-2 text-sm">
                                {{ subscription.plan.price.toLocaleString('ru-RU') }}
                                {{ subscription.plan.currency }} / {{ subscription.plan.interval === 'monthly' ? 'месяц' : 'год' }}
                            </p>
                        </div>
                        <div v-else>
                            <p class="text-sm text-muted-foreground">
                                Тарифный план не найден
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>

