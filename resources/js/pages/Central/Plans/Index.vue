<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PlusIcon } from 'lucide-vue-next';
import { route } from '@/lib/routes';

interface Plan {
    id: number;
    name: string;
    slug: string;
    price: number;
    currency: string;
    interval: string;
    is_active: boolean;
    subscriptions_count?: number;
}

interface Props {
    plans: {
        data: Plan[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Тарифные планы" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Тарифные планы</h1>
                    <p class="text-muted-foreground">
                        Управление тарифными планами
                    </p>
                </div>
                <Link :href="route('central.plans.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Создать план
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все планы</CardTitle>
                    <CardDescription>Список всех тарифных планов</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Название</TableHead>
                                <TableHead>Цена</TableHead>
                                <TableHead>Интервал</TableHead>
                                <TableHead>Подписки</TableHead>
                                <TableHead>Статус</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="plan in plans.data" :key="plan.id">
                                <TableCell class="font-medium">{{ plan.name }}</TableCell>
                                <TableCell>{{ plan.price }} {{ plan.currency }}</TableCell>
                                <TableCell>{{ plan.interval === 'monthly' ? 'Месяц' : 'Год' }}</TableCell>
                                <TableCell>{{ plan.subscriptions_count || 0 }}</TableCell>
                                <TableCell>
                                    <Badge :variant="plan.is_active ? 'default' : 'secondary'">
                                        {{ plan.is_active ? 'Активен' : 'Неактивен' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Link :href="route('central.plans.show', plan.id)">
                                        <Button variant="ghost" size="sm">Просмотр</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="plans.data.length === 0">
                                <TableCell colspan="6" class="text-center py-8 text-muted-foreground">
                                    Нет планов
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
