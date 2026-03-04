<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Label } from '@/components/ui/label';
import { route } from '@/lib/routes';
import { PencilIcon, ArrowLeftIcon } from 'lucide-vue-next';

interface Permission {
    id: number;
    name: string;
}

interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions?: Permission[];
    created_at?: string;
    updated_at?: string;
}

interface Props {
    role: Role;
    permissions: Record<string, Permission[]>;
}

const props = defineProps<Props>();
</script>

<template>
    <Head :title="`Роль: ${role.name}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <Link :href="route('roles.index')">
                        <Button variant="ghost" size="sm">
                            <ArrowLeftIcon class="mr-2 h-4 w-4" />
                            Назад
                        </Button>
                    </Link>
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">{{ role.name }}</h1>
                        <p class="text-muted-foreground">
                            Детальная информация о роли и её разрешениях
                        </p>
                    </div>
                </div>
                <Link :href="route('roles.edit', role.id)">
                    <Button>
                        <PencilIcon class="mr-2 h-4 w-4" />
                        Редактировать
                    </Button>
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
                        <CardDescription>Общие данные о роли</CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Название</Label>
                            <p class="text-lg font-semibold">{{ role.name }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium text-muted-foreground">Guard</Label>
                            <p class="text-lg">{{ role.guard_name }}</p>
                        </div>
                        <div v-if="role.created_at">
                            <Label class="text-sm font-medium text-muted-foreground">Создана</Label>
                            <p class="text-lg">{{ new Date(role.created_at).toLocaleDateString('ru-RU') }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Статистика</CardTitle>
                        <CardDescription>Количество разрешений</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="text-3xl font-bold">
                            {{ role.permissions?.length || 0 }}
                        </div>
                        <p class="text-sm text-muted-foreground mt-2">
                            разрешений назначено
                        </p>
                    </CardContent>
                </Card>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Разрешения</CardTitle>
                    <CardDescription>
                        Список всех разрешений, назначенных этой роли
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="!role.permissions || role.permissions.length === 0" class="text-center py-8 text-muted-foreground">
                        У этой роли нет назначенных разрешений
                    </div>
                    <div v-else class="space-y-4">
                        <div v-for="(group, module) in permissions" :key="module" class="space-y-2">
                            <h3 class="text-lg font-semibold capitalize">{{ module }}</h3>
                            <div class="flex flex-wrap gap-2">
                                <Badge
                                    v-for="permission in group"
                                    :key="permission.id"
                                    :variant="role.permissions?.some(p => p.id === permission.id) ? 'default' : 'outline'"
                                >
                                    {{ permission.name }}
                                </Badge>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
