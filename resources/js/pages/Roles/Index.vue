<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PlusIcon, EyeIcon, PencilIcon, Trash2Icon } from 'lucide-vue-next';
import { route } from '@/lib/routes';

interface Role {
    id: number;
    name: string;
    permissions_count?: number;
    users_count?: number;
    permissions?: Array<{ name: string }>;
}

interface Props {
    roles: {
        data: Role[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const deleteRole = (role: Role) => {
    if (confirm(`Вы уверены, что хотите удалить роль "${role.name}"?`)) {
        router.delete(route('roles.destroy', role.id));
    }
};
</script>

<template>
    <Head title="Роли" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Роли</h1>
                    <p class="text-muted-foreground">
                        Управляйте ролями и разрешениями
                    </p>
                </div>
                <Link :href="route('roles.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Создать роль
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все роли</CardTitle>
                    <CardDescription>Список всех ролей в системе</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Название</TableHead>
                                <TableHead>Разрешения</TableHead>
                                <TableHead>Пользователей</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="role in roles.data" :key="role.id">
                                <TableCell class="font-medium">{{ role.name }}</TableCell>
                                <TableCell>
                                    <Badge variant="secondary">{{ role.permissions_count || 0 }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ role.users_count || 0 }}</Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('roles.show', role.id)">
                                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                                <EyeIcon class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link :href="route('roles.edit', role.id)">
                                            <Button variant="ghost" size="sm" class="h-8 w-8 p-0">
                                                <PencilIcon class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            v-if="role.name !== 'admin'"
                                            variant="ghost"
                                            size="sm"
                                            class="h-8 w-8 p-0 text-destructive hover:text-destructive hover:bg-destructive/10"
                                            @click="deleteRole(role)"
                                        >
                                            <Trash2Icon class="h-4 w-4" />
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
