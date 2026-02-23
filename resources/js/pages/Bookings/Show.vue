<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CalendarIcon, ClockIcon, UserIcon, DollarSignIcon } from 'lucide-vue-next';
import type { Booking } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    booking: Booking;
}

const props = defineProps<Props>();

const form = useForm({
    status: props.booking.status,
    notes: props.booking.notes || '',
});

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'long',
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

const submit = () => {
    form.put(route('bookings.update', props.booking.id));
};
</script>

<template>
    <Head :title="`Бронирование #${booking.booking_number}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">
                        Бронирование #{{ booking.booking_number }}
                    </h1>
                    <p class="text-muted-foreground">
                        Детальная информация о бронировании
                    </p>
                </div>
                <Link :href="route('bookings.index')">
                    <Button variant="outline">Назад к списку</Button>
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Основная информация -->
                <Card>
                    <CardHeader>
                        <CardTitle>Информация о бронировании</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex items-center gap-2">
                            <Badge :variant="statusVariants[booking.status]">
                                {{ statusLabels[booking.status] }}
                            </Badge>
                            <span class="text-sm text-muted-foreground">
                                {{ booking.booking_mode }}
                            </span>
                        </div>

                        <div class="space-y-3">
                            <div class="flex items-center gap-2">
                                <CalendarIcon class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">{{ formatDate(booking.start_time) }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatTime(booking.start_time) }} - {{ formatTime(booking.end_time) }}
                                    </p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <ClockIcon class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">Длительность</p>
                                    <p class="text-xs text-muted-foreground">{{ booking.duration }} минут</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <UserIcon class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">Клиент</p>
                                    <p class="text-xs text-muted-foreground">{{ booking.customer?.name }}</p>
                                </div>
                            </div>

                            <div v-if="booking.staff" class="flex items-center gap-2">
                                <UserIcon class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">Сотрудник</p>
                                    <p class="text-xs text-muted-foreground">{{ booking.staff?.name }}</p>
                                </div>
                            </div>

                            <div class="flex items-center gap-2">
                                <DollarSignIcon class="h-4 w-4 text-muted-foreground" />
                                <div>
                                    <p class="text-sm font-medium">Цена</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ booking.total_price?.toLocaleString('ru-RU') }} ₽
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Услуга и детали -->
                <Card>
                    <CardHeader>
                        <CardTitle>Услуга</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="font-medium">{{ booking.service?.name }}</p>
                            <p class="text-sm text-muted-foreground">{{ booking.service?.description }}</p>
                        </div>

                        <div v-if="booking.notes" class="space-y-2">
                            <p class="text-sm font-medium">Примечания</p>
                            <p class="text-sm text-muted-foreground">{{ booking.notes }}</p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
