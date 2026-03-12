<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
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

interface CentralUser {
    id: number;
    name: string;
    email: string;
}

interface MigrationStatus {
    database_exists: boolean;
    migrations_completed: boolean;
    table_count: number;
    required_tables: string[];
    missing_tables: string[];
    error?: string;
}

interface Props {
    tenant: Tenant;
    users: CentralUser[];
    migrationStatus?: MigrationStatus;
}

const props = defineProps<Props>();

const attachForm = useForm({
    user_id: '',
});

const createDbForm = useForm({});
const updateDbForm = useForm({});

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
                <div class="flex gap-2">
                    <Link :href="route('central.tenants.index')">
                        <Button variant="outline">Назад к списку</Button>
                    </Link>
                    <Link :href="route('central.tenants.edit', tenant.id)">
                        <Button>Редактировать</Button>
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

            <Card>
                <CardHeader>
                    <CardTitle>База данных</CardTitle>
                    <CardDescription>
                        Управление базой данных для tenant
                    </CardDescription>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div v-if="migrationStatus">
                        <div class="space-y-2">
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Статус базы данных:</span>
                                <Badge :variant="migrationStatus.database_exists ? 'default' : 'destructive'">
                                    {{ migrationStatus.database_exists ? 'Создана' : 'Не создана' }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Статус миграций:</span>
                                <Badge :variant="migrationStatus.migrations_completed ? 'default' : 'secondary'">
                                    {{ migrationStatus.migrations_completed ? 'Выполнены' : 'Не выполнены' }}
                                </Badge>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-muted-foreground">Таблиц в базе:</span>
                                <span class="text-sm font-medium">{{ migrationStatus.table_count }}</span>
                            </div>
                            <div v-if="migrationStatus.missing_tables && migrationStatus.missing_tables.length > 0" class="mt-2">
                                <p class="text-sm text-muted-foreground mb-1">Отсутствующие таблицы:</p>
                                <div class="flex flex-wrap gap-1">
                                    <Badge v-for="table in migrationStatus.missing_tables" :key="table" variant="outline">
                                        {{ table }}
                                    </Badge>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="flex gap-2">
                        <form
                            @submit.prevent="createDbForm.post(route('central.tenants.createDatabase', tenant.id))"
                        >
                            <Button 
                                type="submit" 
                                :disabled="createDbForm.processing || (migrationStatus?.migrations_completed && migrationStatus?.database_exists)" 
                                variant="outline"
                            >
                                {{ createDbForm.processing ? 'Создание...' : (migrationStatus?.migrations_completed ? 'Миграции выполнены' : 'Создать базу данных и выполнить миграции') }}
                            </Button>
                        </form>
                        
                        <form
                            v-if="migrationStatus?.database_exists"
                            @submit.prevent="updateDbForm.post(route('central.tenants.updateDatabase', tenant.id))"
                        >
                            <Button 
                                type="submit" 
                                :disabled="updateDbForm.processing" 
                                variant="outline"
                            >
                                {{ updateDbForm.processing ? 'Обновление...' : 'Обновить миграции' }}
                            </Button>
                        </form>
                    </div>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>Администратор tenant</CardTitle>
                    <CardDescription>
                        Привяжите глобального пользователя как администратора этого tenant
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form
                        class="flex flex-col gap-4 md:flex-row md:items-end"
                        @submit.prevent="attachForm.post(route('central.tenants.attachUser', tenant.id))"
                    >
                        <div class="flex-1 space-y-2">
                            <label for="admin_user" class="text-sm font-medium text-muted-foreground">
                                Пользователь
                            </label>
                            <select
                                id="admin_user"
                                v-model="attachForm.user_id"
                                class="flex h-9 w-full rounded-md border border-input bg-background px-3 py-1 text-sm shadow-sm transition-colors focus-visible:outline-none focus-visible:ring-1 focus-visible:ring-ring disabled:cursor-not-allowed disabled:opacity-50"
                            >
                                <option value="">Выберите пользователя</option>
                                <option
                                    v-for="user in users"
                                    :key="user.id"
                                    :value="String(user.id)"
                                >
                                    {{ user.name }} ({{ user.email }})
                                </option>
                            </select>
                            <p v-if="attachForm.errors.user_id" class="text-sm text-destructive">
                                {{ attachForm.errors.user_id }}
                            </p>
                        </div>

                        <Button type="submit" :disabled="attachForm.processing">
                            {{ attachForm.processing ? 'Сохранение...' : 'Назначить администратором' }}
                        </Button>
                    </form>
                </CardContent>
            </Card>

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
                                <TableCell>{{ billing.amount.toLocaleString('ru-RU') }} ₸</TableCell>
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
