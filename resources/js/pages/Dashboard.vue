<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CalendarIcon, ClockIcon, DollarSignIcon, UsersIcon } from 'lucide-vue-next';
import type { Booking, Service, Customer } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    stats: {
        total_bookings: number;
        pending_bookings: number;
        confirmed_bookings: number;
        total_revenue: number;
        total_services: number;
        total_customers: number;
    };
    recentBookings: Booking[];
    upcomingBookings: Booking[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/',
    },
];

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
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <!-- Статистика -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Всего бронирований</CardTitle>
                        <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_bookings }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ stats.pending_bookings }} ожидают подтверждения
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Доходы</CardTitle>
                        <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ stats.total_revenue.toLocaleString('ru-RU') }} ₸
                        </div>
                        <p class="text-xs text-muted-foreground">Всего заработано</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Активные клиенты</CardTitle>
                        <UsersIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_customers }}</div>
                        <p class="text-xs text-muted-foreground">Всего клиентов</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Услуги</CardTitle>
                        <ClockIcon class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ stats.total_services }}</div>
                        <p class="text-xs text-muted-foreground">Активных услуг</p>
                    </CardContent>
                </Card>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Последние бронирования -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Последние бронирования</CardTitle>
                                <CardDescription>Недавно созданные бронирования</CardDescription>
                            </div>
                            <Link :href="route('bookings.index')">
                                <Button variant="outline" size="sm">Все</Button>
                            </Link>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="booking in recentBookings"
                                :key="booking.id"
                                class="flex items-center justify-between border-b pb-4 last:border-0"
                            >
                                <div class="space-y-1">
                                    <p class="font-medium">{{ booking.service?.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ booking.customer?.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(booking.start_time) }}
                                        {{ formatTime(booking.start_time) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <Badge :variant="statusVariants[booking.status]">
                                        {{ statusLabels[booking.status] }}
                                    </Badge>
                                    <p class="mt-1 text-sm font-medium">
                                        {{ booking.total_price?.toLocaleString('ru-RU') }} ₸
                                    </p>
                                </div>
                            </div>
                            <div v-if="recentBookings.length === 0" class="text-center py-8 text-muted-foreground">
                                Нет бронирований
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Предстоящие бронирования -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle>Предстоящие</CardTitle>
                                <CardDescription>Ближайшие бронирования</CardDescription>
                            </div>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="booking in upcomingBookings"
                                :key="booking.id"
                                class="flex items-center justify-between border-b pb-4 last:border-0"
                            >
                                <div class="space-y-1">
                                    <p class="font-medium">{{ booking.service?.name }}</p>
                                    <p class="text-sm text-muted-foreground">
                                        {{ booking.customer?.name }}
                                    </p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(booking.start_time) }}
                                        {{ formatTime(booking.start_time) }}
                                    </p>
                                </div>
                                <Link :href="route('bookings.show', booking.id)">
                                    <Button variant="ghost" size="sm">Просмотр</Button>
                                </Link>
                            </div>
                            <div v-if="upcomingBookings.length === 0" class="text-center py-8 text-muted-foreground">
                                Нет предстоящих бронирований
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
