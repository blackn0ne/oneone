<script setup lang="ts">
import { computed } from 'vue';
import { Head, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { BarChart3, Calendar, Briefcase, Users, UserCircle, Filter, X } from 'lucide-vue-next';
import { route } from '@/lib/routes';

interface Props {
    bookingsStats: {
        total: number;
        today: number;
        this_week: number;
        this_month: number;
        pending: number;
        confirmed: number;
        completed: number;
        cancelled: number;
        filtered?: number;
    };
    servicesStats: {
        total: number;
        active: number;
    };
    staffStats: {
        total: number;
        active: number;
    };
    customersStats: {
        total: number;
    };
    topServices: Array<{
        id: number;
        name: string;
        bookings_count: number;
    }>;
    topStaff: Array<{
        id: number;
        name: string;
        bookings_count: number;
    }>;
    monthlyBookings: Array<{
        month: string;
        count: number;
    }>;
    filters: {
        date_from?: string;
        date_to?: string;
        status?: string;
        service_id?: number;
        staff_id?: number;
    };
    services: Array<{
        id: number;
        name: string;
    }>;
    staffList: Array<{
        id: number;
        name: string;
    }>;
}

const props = defineProps<Props>();

const form = useForm({
    date_from: props.filters.date_from || '',
    date_to: props.filters.date_to || '',
    status: props.filters.status || null,
    service_id: props.filters.service_id ? String(props.filters.service_id) : null,
    staff_id: props.filters.staff_id ? String(props.filters.staff_id) : null,
});

const applyFilters = () => {
    const filters: Record<string, any> = {};
    
    if (form.date_from) filters.date_from = form.date_from;
    if (form.date_to) filters.date_to = form.date_to;
    if (form.status) filters.status = form.status;
    if (form.service_id) filters.service_id = form.service_id;
    if (form.staff_id) filters.staff_id = form.staff_id;
    
    router.get(route('reports.index'), filters, {
        preserveState: true,
        preserveScroll: true,
    });
};

const clearFilters = () => {
    form.reset();
    router.get(route('reports.index'), {}, {
        preserveState: true,
        preserveScroll: true,
    });
};

const hasActiveFilters = computed(() => {
    return !!(form.date_from || form.date_to || form.status || form.service_id || form.staff_id);
});
</script>

<template>
    <Head title="Отчеты" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Отчеты</h1>
                    <p class="text-muted-foreground">
                        Статистика и аналитика по вашему бизнесу
                    </p>
                </div>
            </div>

            <!-- Фильтры -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-5 w-5" />
                        Фильтры
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                        <div class="space-y-2">
                            <Label for="date_from">Дата от</Label>
                            <Input
                                id="date_from"
                                v-model="form.date_from"
                                type="date"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="date_to">Дата до</Label>
                            <Input
                                id="date_to"
                                v-model="form.date_to"
                                type="date"
                                @keyup.enter="applyFilters"
                            />
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Статус</Label>
                            <Select v-model="form.status">
                                <SelectTrigger id="status">
                                    <SelectValue placeholder="Все статусы" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">Все статусы</SelectItem>
                                    <SelectItem value="pending">Ожидают</SelectItem>
                                    <SelectItem value="confirmed">Подтверждены</SelectItem>
                                    <SelectItem value="completed">Завершены</SelectItem>
                                    <SelectItem value="cancelled">Отменены</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="service_id">Услуга</Label>
                            <Select v-model="form.service_id">
                                <SelectTrigger id="service_id">
                                    <SelectValue placeholder="Все услуги" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">Все услуги</SelectItem>
                                    <SelectItem
                                        v-for="service in services"
                                        :key="service.id"
                                        :value="String(service.id)"
                                    >
                                        {{ service.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="staff_id">Сотрудник</Label>
                            <Select v-model="form.staff_id">
                                <SelectTrigger id="staff_id">
                                    <SelectValue placeholder="Все сотрудники" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem :value="null">Все сотрудники</SelectItem>
                                    <SelectItem
                                        v-for="staff in staffList"
                                        :key="staff.id"
                                        :value="String(staff.id)"
                                    >
                                        {{ staff.name }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>

                    <div class="flex items-center justify-end gap-2 mt-4">
                        <Button
                            v-if="hasActiveFilters"
                            variant="outline"
                            size="sm"
                            @click="clearFilters"
                        >
                            <X class="mr-2 h-4 w-4" />
                            Сбросить
                        </Button>
                        <Button size="sm" @click="applyFilters" :disabled="form.processing">
                            {{ form.processing ? 'Применение...' : 'Применить фильтры' }}
                        </Button>
                    </div>

                    <div v-if="hasActiveFilters && bookingsStats.filtered !== undefined" class="mt-4 text-sm text-muted-foreground">
                        Найдено бронирований по фильтрам: <strong>{{ bookingsStats.filtered }}</strong>
                    </div>
                </CardContent>
            </Card>

            <!-- Статистика по бронированиям -->
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Всего бронирований</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ bookingsStats.total }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Сегодня</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ bookingsStats.today }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">На этой неделе</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ bookingsStats.this_week }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">В этом месяце</CardTitle>
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ bookingsStats.this_month }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Статусы бронирований -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Ожидают</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ bookingsStats.pending }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Подтверждены</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ bookingsStats.confirmed }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Завершены</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ bookingsStats.completed }}</div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Отменены</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ bookingsStats.cancelled }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Общая статистика -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Услуги</CardTitle>
                        <Briefcase class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ servicesStats.total }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ servicesStats.active }} активных
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Сотрудники</CardTitle>
                        <UserCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ staffStats.total }}</div>
                        <p class="text-xs text-muted-foreground">
                            {{ staffStats.active }} активных
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Клиенты</CardTitle>
                        <Users class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ customersStats.total }}</div>
                    </CardContent>
                </Card>
            </div>

            <!-- Топ услуги и сотрудники -->
            <div class="grid gap-4 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Топ услуги</CardTitle>
                        <CardDescription>
                            Самые популярные услуги по количеству бронирований
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="topServices.length === 0" class="text-center py-8 text-muted-foreground">
                            Нет данных
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="(service, index) in topServices"
                                :key="service.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted">
                                        <span class="text-sm font-semibold">{{ index + 1 }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ service.name }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold">{{ service.bookings_count }}</p>
                                    <p class="text-xs text-muted-foreground">бронирований</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Топ сотрудники</CardTitle>
                        <CardDescription>
                            Самые загруженные сотрудники по количеству бронирований
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div v-if="topStaff.length === 0" class="text-center py-8 text-muted-foreground">
                            Нет данных
                        </div>
                        <div v-else class="space-y-4">
                            <div
                                v-for="(staff, index) in topStaff"
                                :key="staff.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-full bg-muted">
                                        <span class="text-sm font-semibold">{{ index + 1 }}</span>
                                    </div>
                                    <div>
                                        <p class="font-medium">{{ staff.name }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="font-semibold">{{ staff.bookings_count }}</p>
                                    <p class="text-xs text-muted-foreground">бронирований</p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
