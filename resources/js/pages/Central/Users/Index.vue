<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PlusIcon } from 'lucide-vue-next';
import { route } from '@/lib/routes';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

interface User {
    id: number;
    name: string;
    email: string;
    role?: 'super_admin' | 'admin' | 'staff';
    is_super_admin?: boolean;
    roles?: Array<{ name: string }>;
    tenant_connections?: Array<{
        tenant_id: string;
        tenant_name: string;
        staff_name: string;
    }>;
}

interface Props {
    users: {
        data: User[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();
</script>

<template>
    <Head :title="t('users.title', 'Пользователи')" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ t('users.title', 'Пользователи') }}</h1>
                    <p class="text-muted-foreground">
                        {{ t('users.description', 'Управление пользователями системы') }}
                    </p>
                </div>
                <Link :href="route('central.users.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        {{ t('users.create', 'Создать пользователя') }}
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('users.list_title', 'Все пользователи') }}</CardTitle>
                    <CardDescription>{{ t('users.list_description', 'Список всех пользователей системы') }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>{{ t('users.name', 'Имя') }}</TableHead>
                                <TableHead>{{ t('users.email', 'Email') }}</TableHead>
                                <TableHead>{{ t('users.roles', 'Роли') }}</TableHead>
                                <TableHead>{{ t('users.tenants', 'Tenants') }}</TableHead>
                                <TableHead>{{ t('users.status', 'Статус') }}</TableHead>
                                <TableHead class="text-right">{{ t('users.actions', 'Действия') }}</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="user in users.data" :key="user.id">
                                <TableCell class="font-medium">{{ user.name }}</TableCell>
                                <TableCell>{{ user.email }}</TableCell>
                                <TableCell>
                                    <div class="flex flex-wrap gap-1">
                                        <Badge v-if="user.role === 'super_admin' || user.is_super_admin" variant="default">
                                            {{ t('users.super_admin', 'Супер-админ') }}
                                        </Badge>
                                        <Badge v-for="role in user.roles" :key="role.name" variant="secondary">
                                            {{ role.name }}
                                        </Badge>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <div v-if="user.tenant_connections && user.tenant_connections.length > 0" class="flex flex-col gap-1">
                                        <Badge v-for="conn in user.tenant_connections" :key="conn.tenant_id" variant="outline">
                                            {{ conn.tenant_name }}
                                        </Badge>
                                    </div>
                                    <span v-else class="text-muted-foreground">-</span>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="default">{{ t('users.active', 'Активен') }}</Badge>
                                </TableCell>
                                <TableCell class="text-right space-x-2">
                                    <Link :href="route('central.users.show', user.id)">
                                        <Button variant="ghost" size="sm">{{ t('users.view', 'Просмотр') }}</Button>
                                    </Link>
                                    <Link :href="route('central.users.edit', user.id)">
                                        <Button variant="outline" size="sm">{{ t('users.edit', 'Редактировать') }}</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="users.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    {{ t('users.no_users', 'Нет пользователей') }}
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
