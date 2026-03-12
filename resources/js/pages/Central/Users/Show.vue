<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { route } from '@/lib/routes';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

interface TenantConnection {
    tenant_id: string;
    tenant_name: string;
    staff_id: number;
    staff_name: string;
    staff_email?: string;
    staff_phone?: string;
    is_active: boolean;
}

interface User {
    id: number;
    name: string;
    email: string;
    role?: 'super_admin' | 'admin' | 'staff';
    is_super_admin?: boolean;
    roles?: Array<{ name: string }>;
    tenant_connections?: TenantConnection[];
}

interface Props {
    user: User;
}

const props = defineProps<Props>();

const deleteUser = () => {
    if (confirm(t('users.delete_confirm', 'Вы уверены, что хотите удалить этого пользователя?'))) {
        router.delete(route('central.users.destroy', props.user.id));
    }
};
</script>

<template>
    <Head :title="user.name" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ user.name }}</h1>
                    <p class="text-muted-foreground">
                        {{ t('users.user_info', 'Информация о пользователе') }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('central.users.index')">
                        <Button variant="outline">{{ t('users.back', 'Назад к списку') }}</Button>
                    </Link>
                    <Link :href="route('central.users.edit', user.id)">
                        <Button>{{ t('users.edit', 'Редактировать') }}</Button>
                    </Link>
                    <Button v-if="user.role !== 'super_admin' && !user.is_super_admin" variant="destructive" @click="deleteUser">
                        {{ t('users.delete', 'Удалить') }}
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('users.basic_info', 'Основная информация') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('users.name', 'Имя') }}</p>
                            <p class="text-lg">{{ user.name }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('users.email', 'Email') }}</p>
                            <p>{{ user.email }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">{{ t('users.status', 'Статус') }}</p>
                            <div class="flex flex-wrap gap-2 mt-1">
                                <Badge v-if="user.role === 'super_admin' || user.is_super_admin" variant="default">
                                    {{ t('users.super_admin', 'Супер-админ') }}
                                </Badge>
                                <Badge v-for="role in user.roles" :key="role.name" variant="secondary">
                                    {{ role.name }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{{ t('users.tenant_connections', 'Связи с Tenant') }}</CardTitle>
                        <CardDescription>
                            {{ user.tenant_connections?.length || 0 }} {{ t('users.connections', 'связей') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="user.tenant_connections && user.tenant_connections.length > 0" class="space-y-4">
                            <div
                                v-for="conn in user.tenant_connections"
                                :key="conn.tenant_id"
                                class="border rounded-lg p-4 space-y-2"
                            >
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="font-medium">{{ conn.tenant_name }}</p>
                                        <p class="text-sm text-muted-foreground font-mono">{{ conn.tenant_id }}</p>
                                    </div>
                                    <Badge :variant="conn.is_active ? 'default' : 'secondary'">
                                        {{ conn.is_active ? t('users.active', 'Активен') : t('users.inactive', 'Неактивен') }}
                                    </Badge>
                                </div>
                                <div class="pt-2 border-t">
                                    <p class="text-sm font-medium">{{ t('users.staff_info', 'Информация о сотруднике') }}</p>
                                    <p class="text-sm text-muted-foreground">{{ conn.staff_name }}</p>
                                    <p v-if="conn.staff_email" class="text-sm text-muted-foreground">{{ conn.staff_email }}</p>
                                    <p v-if="conn.staff_phone" class="text-sm text-muted-foreground">{{ conn.staff_phone }}</p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">{{ t('users.no_connections', 'Нет связей с tenant') }}</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
