<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { route } from '@/lib/routes';

interface Tenant {
    id: string;
    name: string;
    email: string;
    phone?: string;
    status: 'active' | 'suspended' | 'trial';
    plan?: {
        id: number;
        name: string;
    };
    subscription?: {
        id: number;
        status: string;
        starts_at: string;
        ends_at?: string;
    };
    domains?: Array<{
        id: number;
        domain: string;
    }>;
    billings?: Array<{
        id: number;
        amount: number;
        status: string;
        created_at: string;
    }>;
}

interface Props {
    tenant: Tenant;
}

const props = defineProps<Props>();

const statusLabels = {
    active: 'Активен',
    suspended: 'Приостановлен',
    trial: 'Пробный период',
};

const statusVariants = {
    active: 'default',
    suspended: 'destructive',
    trial: 'secondary',
} as const;
</script>

<template>
    <Head :title="tenant.name" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ tenant.name }}</h1>
                    <p class="text-muted-foreground">
                        Информация о tenant
                    </p>
                </div>
                <Link :href="route('central.tenants.index')">
                    <Button variant="outline">Назад к списку</Button>
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">ID</p>
                            <p class="font-mono text-sm">{{ tenant.id }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Название</p>
                            <p class="text-lg">{{ tenant.name }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Email</p>
                            <p>{{ tenant.email }}</p>
                        </div>

                        <div v-if="tenant.phone">
                            <p class="text-sm font-medium text-muted-foreground">Телефон</p>
                            <p>{{ tenant.phone }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Статус</p>
                            <Badge :variant="statusVariants[tenant.status]">
                                {{ statusLabels[tenant.status] }}
                            </Badge>
                        </div>

                        <div v-if="tenant.plan">
                            <p class="text-sm font-medium text-muted-foreground">Тарифный план</p>
                            <p>{{ tenant.plan.name }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Домены</CardTitle>
                        <CardDescription>
                            {{ tenant.domains?.length || 0 }} доменов
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="tenant.domains && tenant.domains.length > 0" class="space-y-2">
                            <div
                                v-for="domain in tenant.domains"
                                :key="domain.id"
                                class="flex items-center justify-between border-b pb-2"
                            >
                                <span class="font-mono text-sm">{{ domain.domain }}</span>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Нет доменов</p>
                    </CardContent>
                </Card>
            </div>

            <Card v-if="tenant.billings && tenant.billings.length > 0">
                <CardHeader>
                    <CardTitle>История платежей</CardTitle>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Дата</TableHead>
                                <TableHead>Сумма</TableHead>
                                <TableHead>Статус</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="billing in tenant.billings" :key="billing.id">
                                <TableCell>
                                    {{ new Date(billing.created_at).toLocaleDateString('ru-RU') }}
                                </TableCell>
                                <TableCell>{{ billing.amount.toLocaleString('ru-RU') }} ₽</TableCell>
                                <TableCell>
                                    <Badge :variant="billing.status === 'paid' ? 'default' : 'secondary'">
                                        {{ billing.status }}
                                    </Badge>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
