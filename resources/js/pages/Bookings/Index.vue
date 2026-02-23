<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { PlusIcon, SearchIcon } from 'lucide-vue-next';
import type { Booking } from '@/types';
import { ref } from 'vue';
import { route } from '@/lib/routes';

interface Props {
    bookings: {
        data: Booking[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const searchQuery = ref('');

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatTime = (date: string) => {
    return new Date(date).toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const statusLabels = {
    pending: 'Ожидает',
    confirmed: 'Подтверждено',
    cancelled: 'Отменено',
    completed: 'Завершено',
    no_show: 'Не явился',
};

const statusVariants = {
    pending: 'secondary',
    confirmed: 'default',
    cancelled: 'destructive',
    completed: 'default',
    no_show: 'destructive',
} as const;

const handleDelete = (booking: Booking) => {
    if (confirm('Вы уверены, что хотите удалить это бронирование?')) {
        router.delete(route('bookings.destroy', booking.id));
    }
};
</script>

<template>
    <Head title="Бронирования" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Бронирования</h1>
                    <p class="text-muted-foreground">
                        Управляйте всеми бронированиями
                    </p>
                </div>
                <Link :href="route('bookings.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Новое бронирование
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Все бронирования</CardTitle>
                            <CardDescription>Список всех бронирований</CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="relative">
                                <SearchIcon class="absolute left-2 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    v-model="searchQuery"
                                    placeholder="Поиск..."
                                    class="pl-8 w-64"
                                />
                            </div>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Номер</TableHead>
                                <TableHead>Услуга</TableHead>
                                <TableHead>Клиент</TableHead>
                                <TableHead>Сотрудник</TableHead>
                                <TableHead>Дата и время</TableHead>
                                <TableHead>Статус</TableHead>
                                <TableHead>Цена</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="booking in bookings.data" :key="booking.id">
                                <TableCell class="font-mono text-sm">
                                    {{ booking.booking_number }}
                                </TableCell>
                                <TableCell class="font-medium">
                                    {{ booking.service?.name }}
                                </TableCell>
                                <TableCell>{{ booking.customer?.name }}</TableCell>
                                <TableCell>{{ booking.staff?.name || '-' }}</TableCell>
                                <TableCell>
                                    <div class="text-sm">
                                        <div>{{ formatDate(booking.start_time) }}</div>
                                        <div class="text-muted-foreground">
                                            {{ formatTime(booking.start_time) }} -
                                            {{ formatTime(booking.end_time) }}
                                        </div>
                                    </div>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="statusVariants[booking.status]">
                                        {{ statusLabels[booking.status] }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ booking.total_price?.toLocaleString('ru-RU') }} ₽
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('bookings.show', booking.id)">
                                            <Button variant="ghost" size="sm">Просмотр</Button>
                                        </Link>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="handleDelete(booking)"
                                        >
                                            Удалить
                                        </Button>
                                    </div>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="bookings.data.length === 0">
                                <TableCell colspan="8" class="text-center py-8 text-muted-foreground">
                                    Нет бронирований
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div v-if="bookings.last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Страница {{ bookings.current_page }} из {{ bookings.last_page }}
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in bookings.links"
                                :key="link.label"
                                :href="link.url || '#'"
                                :class="[
                                    'px-3 py-2 text-sm rounded-md',
                                    link.active
                                        ? 'bg-primary text-primary-foreground'
                                        : 'bg-secondary text-secondary-foreground hover:bg-secondary/80',
                                    !link.url && 'opacity-50 cursor-not-allowed',
                                ]"
                                v-html="link.label"
                            />
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
