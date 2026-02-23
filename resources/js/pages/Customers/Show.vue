<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Customer } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    customer: Customer;
}

const props = defineProps<Props>();

const formatDate = (date: string) => {
    return new Date(date).toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};
</script>

<template>
    <Head :title="customer.name" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ customer.name }}</h1>
                    <p class="text-muted-foreground">
                        Информация о клиенте
                    </p>
                </div>
                <Link :href="route('customers.index')">
                    <Button variant="outline">Назад к списку</Button>
                </Link>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Контактная информация</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Имя</p>
                            <p class="text-lg">{{ customer.name }}</p>
                        </div>

                        <div v-if="customer.email">
                            <p class="text-sm font-medium text-muted-foreground">Email</p>
                            <p>{{ customer.email }}</p>
                        </div>

                        <div v-if="customer.phone">
                            <p class="text-sm font-medium text-muted-foreground">Телефон</p>
                            <p>{{ customer.phone }}</p>
                        </div>

                        <div v-if="customer.address">
                            <p class="text-sm font-medium text-muted-foreground">Адрес</p>
                            <p>{{ customer.address }}</p>
                        </div>

                        <div v-if="customer.notes">
                            <p class="text-sm font-medium text-muted-foreground">Примечания</p>
                            <p>{{ customer.notes }}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>История бронирований</CardTitle>
                        <CardDescription>
                            {{ customer.bookings?.length || 0 }} бронирований
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="customer.bookings && customer.bookings.length > 0" class="space-y-2">
                            <div
                                v-for="booking in customer.bookings"
                                :key="booking.id"
                                class="flex items-center justify-between border-b pb-2"
                            >
                                <div>
                                    <p class="font-medium">{{ booking.service?.name }}</p>
                                    <p class="text-xs text-muted-foreground">
                                        {{ formatDate(booking.start_time) }}
                                    </p>
                                </div>
                                <div class="text-right">
                                    <Badge :variant="booking.status === 'confirmed' ? 'default' : 'secondary'">
                                        {{ booking.status }}
                                    </Badge>
                                    <p class="text-sm mt-1">{{ booking.total_price }} ₽</p>
                                </div>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Нет бронирований</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
