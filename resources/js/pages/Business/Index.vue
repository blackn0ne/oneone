<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PlusIcon, Pencil, Trash2, MapPin } from 'lucide-vue-next';
import type { Business } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    businesses: {
        data: Business[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const handleDelete = (business: Business) => {
    if (confirm('Вы уверены, что хотите удалить эту точку продаж?')) {
        router.delete(route('business.destroy', business.id));
    }
};
</script>

<template>
    <Head title="Точки продаж" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Точки продаж</h1>
                    <p class="text-muted-foreground">
                        Управляйте точками продаж и их настройками
                    </p>
                </div>
                <Link :href="route('business.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Добавить точку продаж
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все точки продаж</CardTitle>
                    <CardDescription>Список всех точек продаж</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Название</TableHead>
                                <TableHead>Адрес</TableHead>
                                <TableHead>Телефон</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Статус</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-if="businesses.data.length === 0">
                                <TableCell colspan="6" class="text-center text-muted-foreground">
                                    Нет точек продаж. Создайте первую точку продаж.
                                </TableCell>
                            </TableRow>
                            <TableRow v-for="business in businesses.data" :key="business.id">
                                <TableCell class="font-medium">
                                    <div class="flex items-center gap-2">
                                        <MapPin class="h-4 w-4 text-muted-foreground" />
                                        {{ business.name }}
                                    </div>
                                </TableCell>
                                <TableCell>{{ business.address || '-' }}</TableCell>
                                <TableCell>{{ business.phone || '-' }}</TableCell>
                                <TableCell>{{ business.email || '-' }}</TableCell>
                                <TableCell>
                                    <Badge :variant="business.is_active ? 'default' : 'secondary'">
                                        {{ business.is_active ? 'Активна' : 'Неактивна' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex items-center justify-end gap-2">
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="rounded-full"
                                            :as-child="true"
                                        >
                                            <Link :href="route('business.edit', business.id)">
                                                <Pencil class="h-4 w-4" />
                                            </Link>
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="rounded-full text-destructive hover:text-destructive"
                                            @click="handleDelete(business)"
                                        >
                                            <Trash2 class="h-4 w-4" />
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
