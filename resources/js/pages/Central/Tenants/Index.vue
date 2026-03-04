<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PlusIcon, EyeIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next';
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
    };
}

interface Props {
    tenants: {
        data: Tenant[];
        links: any[];
        current_page: number;
        last_page: number;
    };
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

const deleteTenant = (tenant: Tenant) => {
    if (confirm(`Вы уверены, что хотите удалить tenant "${tenant.name}"? Это действие нельзя отменить.`)) {
        router.delete(route('central.tenants.destroy', tenant.id));
    }
};
</script>

<template>
    <Head title="Tenants" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Tenants</h1>
                    <p class="text-muted-foreground">
                        Управляйте клиентами платформы
                    </p>
                </div>
                <Link :href="route('central.tenants.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Создать Tenant
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все Tenants</CardTitle>
                    <CardDescription>Список всех клиентов платформы</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>ID</TableHead>
                                <TableHead>Название</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Телефон</TableHead>
                                <TableHead>Тариф</TableHead>
                                <TableHead>Статус</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="tenant in tenants.data" :key="tenant.id">
                                <TableCell class="font-mono text-sm">{{ tenant.id }}</TableCell>
                                <TableCell class="font-medium">{{ tenant.name }}</TableCell>
                                <TableCell>{{ tenant.email }}</TableCell>
                                <TableCell>{{ tenant.phone || '-' }}</TableCell>
                                <TableCell>{{ tenant.plan?.name || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="statusVariants[tenant.status]">
                                        {{ statusLabels[tenant.status] }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Link :href="route('central.tenants.show', tenant.id)">
                                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                                <EyeIcon class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link :href="route('central.tenants.edit', tenant.id)">
                                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                                <PencilIcon class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button 
                                            variant="ghost" 
                                            size="sm" 
                                            class="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
                                            @click="deleteTenant(tenant)"
                                        >
                                            <Trash2Icon class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="tenants.data.length === 0">
                                <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                    Нет tenants
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
