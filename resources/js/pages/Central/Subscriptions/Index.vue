<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { route } from '@/lib/routes';

interface Subscription {
    id: number;
    status: string;
    starts_at?: string;
    ends_at?: string;
    tenant?: {
        id: string;
        name: string;
    };
    plan?: {
        id: number;
        name: string;
    };
}

interface Props {
    subscriptions: {
        data: Subscription[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('ru-RU');
};
</script>

<template>
    <Head title="Подписки" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Подписки</h1>
                <p class="text-muted-foreground">
                    Управление подписками tenants
                </p>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все подписки</CardTitle>
                    <CardDescription>Список всех подписок</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Tenant</TableHead>
                                <TableHead>План</TableHead>
                                <TableHead>Начало</TableHead>
                                <TableHead>Окончание</TableHead>
                                <TableHead>Статус</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="subscription in subscriptions.data" :key="subscription.id">
                                <TableCell class="font-medium">
                                    {{ subscription.tenant?.name || '-' }}
                                </TableCell>
                                <TableCell>{{ subscription.plan?.name || '-' }}</TableCell>
                                <TableCell>
                                    {{ subscription.starts_at ? formatDate(subscription.starts_at) : '-' }}
                                </TableCell>
                                <TableCell>
                                    {{ subscription.ends_at ? formatDate(subscription.ends_at) : '-' }}
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="subscription.status === 'active' ? 'default' : 'secondary'">
                                        {{ subscription.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Link :href="route('central.subscriptions.show', subscription.id)">
                                        <Button variant="ghost" size="sm">Просмотр</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="subscriptions.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    Нет подписок
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
