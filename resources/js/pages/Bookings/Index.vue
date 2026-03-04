<script setup lang="ts">
import { computed, ref } from 'vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { PlusIcon, SearchIcon, Calendar as CalendarIcon, List, ChevronLeft, ChevronRight } from 'lucide-vue-next';
import BookingCreateForm from '@/components/Forms/BookingCreateForm.vue';
import type { Booking, Service, Staff } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    bookings: {
        data?: Booking[];
        links?: any[];
        current_page?: number;
        last_page?: number;
    } | Booking[];
    view: 'calendar' | 'list';
    startDate?: string;
    endDate?: string;
    services?: Service[];
    staff?: Staff[];
}

const props = defineProps<Props>();

const searchQuery = ref('');
const currentView = ref<'calendar' | 'list'>(props.view || 'calendar');
const isCreateSheetOpen = ref(false);

const openCreateSheet = () => {
    isCreateSheetOpen.value = true;
};

const closeCreateSheet = (open: boolean) => {
    if (!open) {
        isCreateSheetOpen.value = false;
    }
};

const handleSuccess = () => {
    isCreateSheetOpen.value = false;
};

// Для календарного вида
const currentWeekStart = ref(props.startDate ? new Date(props.startDate) : getStartOfWeek(new Date()));
const hours = Array.from({ length: 24 }, (_, i) => i);
const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

function getStartOfWeek(date: Date): Date {
    const d = new Date(date);
    const day = d.getDay();
    const diff = d.getDate() - day + (day === 0 ? -6 : 1); // Понедельник
    return new Date(d.setDate(diff));
}

function getWeekDates(startDate: Date): Date[] {
    const dates: Date[] = [];
    for (let i = 0; i < 7; i++) {
        const date = new Date(startDate);
        date.setDate(startDate.getDate() + i);
        dates.push(date);
    }
    return dates;
}

const weekDates = computed(() => getWeekDates(currentWeekStart.value));

const bookingsArray = computed(() => {
    if (Array.isArray(props.bookings)) {
        return props.bookings;
    }
    return props.bookings?.data || [];
});

const bookingsByDateAndTime = computed(() => {
    const grouped: Record<string, Record<string, Booking[]>> = {};
    
    bookingsArray.value.forEach((booking: Booking) => {
        const startDate = new Date(booking.start_time);
        const dateKey = startDate.toISOString().split('T')[0];
        const hour = startDate.getHours();
        const hourKey = String(hour);
        
        if (!grouped[dateKey]) {
            grouped[dateKey] = {};
        }
        if (!grouped[dateKey][hourKey]) {
            grouped[dateKey][hourKey] = [];
        }
        grouped[dateKey][hourKey].push(booking);
    });
    
    return grouped;
});

const formatDate = (date: string | Date) => {
    const d = date instanceof Date ? date : new Date(date);
    return d.toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatTime = (date: string | Date) => {
    const d = date instanceof Date ? date : new Date(date);
    return d.toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const formatDateKey = (date: Date) => {
    return date.toISOString().split('T')[0];
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

const statusColors = {
    pending: 'bg-yellow-100 border-yellow-300 text-yellow-800',
    confirmed: 'bg-green-100 border-green-300 text-green-800',
    cancelled: 'bg-red-100 border-red-300 text-red-800',
    completed: 'bg-blue-100 border-blue-300 text-blue-800',
    no_show: 'bg-gray-100 border-gray-300 text-gray-800',
};

const handleDelete = (booking: Booking) => {
    if (confirm('Вы уверены, что хотите удалить это бронирование?')) {
        router.delete(route('bookings.destroy', booking.id));
    }
};

const switchView = (view: 'calendar' | 'list') => {
    currentView.value = view;
    router.get(route('bookings.index'), { view }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const previousWeek = () => {
    const newDate = new Date(currentWeekStart.value);
    newDate.setDate(newDate.getDate() - 7);
    currentWeekStart.value = getStartOfWeek(newDate);
    loadWeekBookings();
};

const nextWeek = () => {
    const newDate = new Date(currentWeekStart.value);
    newDate.setDate(newDate.getDate() + 7);
    currentWeekStart.value = getStartOfWeek(newDate);
    loadWeekBookings();
};

const goToToday = () => {
    currentWeekStart.value = getStartOfWeek(new Date());
    loadWeekBookings();
};

const loadWeekBookings = () => {
    const startDate = formatDateKey(currentWeekStart.value);
    const endDate = new Date(currentWeekStart.value);
    endDate.setDate(endDate.getDate() + 6);
    const endDateStr = formatDateKey(endDate);
    
    router.get(route('bookings.index'), {
        view: 'calendar',
        start_date: startDate,
        end_date: endDateStr,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const getBookingDuration = (booking: Booking) => {
    const start = new Date(booking.start_time);
    const end = new Date(booking.end_time);
    const duration = Math.round((end.getTime() - start.getTime()) / (1000 * 60));
    return duration;
};

const getBookingTop = (booking: Booking) => {
    const start = new Date(booking.start_time);
    const minutes = start.getMinutes();
    return (minutes / 60) * 100;
};

const getBookingHeight = (booking: Booking) => {
    return (getBookingDuration(booking) / 60) * 100;
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
                <div class="flex items-center gap-2">
                    <div class="flex items-center gap-1 border rounded-md">
                        <Button
                            variant="ghost"
                            size="sm"
                            :class="currentView === 'calendar' ? 'bg-accent' : ''"
                            @click="switchView('calendar')"
                        >
                            <CalendarIcon class="mr-2 h-4 w-4" />
                            Календарь
                        </Button>
                        <Button
                            variant="ghost"
                            size="sm"
                            :class="currentView === 'list' ? 'bg-accent' : ''"
                            @click="switchView('list')"
                        >
                            <List class="mr-2 h-4 w-4" />
                            Список
                        </Button>
                    </div>
                    <Button @click="openCreateSheet">
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Новое бронирование
                    </Button>
                </div>
            </div>

            <!-- Календарный вид -->
            <Card v-if="currentView === 'calendar'">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div>
                            <CardTitle>Календарь бронирований</CardTitle>
                            <CardDescription>
                                {{ formatDate(weekDates[0]) }} - {{ formatDate(weekDates[6]) }}
                            </CardDescription>
                        </div>
                        <div class="flex items-center gap-2">
                            <Button variant="outline" size="sm" @click="previousWeek">
                                <ChevronLeft class="h-4 w-4" />
                            </Button>
                            <Button variant="outline" size="sm" @click="goToToday">
                                Сегодня
                            </Button>
                            <Button variant="outline" size="sm" @click="nextWeek">
                                <ChevronRight class="h-4 w-4" />
                            </Button>
                        </div>
                    </div>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <div class="min-w-[800px]">
                            <!-- Заголовки дней недели -->
                            <div class="grid grid-cols-8 border-b">
                                <div class="p-2 font-medium text-sm text-muted-foreground">Время</div>
                                <div
                                    v-for="(day, index) in weekDates"
                                    :key="index"
                                    class="p-2 text-center border-l"
                                >
                                    <div class="font-medium">{{ weekDays[index] }}</div>
                                    <div class="text-sm text-muted-foreground">{{ day.getDate() }}</div>
                                </div>
                            </div>

                            <!-- Временные слоты -->
                            <div class="grid grid-cols-8 border-b" v-for="hour in hours" :key="hour">
                                <div class="p-2 text-sm text-muted-foreground border-r">
                                    {{ String(hour).padStart(2, '0') }}:00
                                </div>
                                <div
                                    v-for="(day, dayIndex) in weekDates"
                                    :key="dayIndex"
                                    class="relative border-l border-b min-h-[60px] p-1"
                                    :class="day.toDateString() === new Date().toDateString() ? 'bg-blue-50' : ''"
                                >
                                    <div
                                        v-for="booking in (bookingsByDateAndTime[formatDateKey(day)]?.[String(hour)] || [])"
                                        :key="booking.id"
                                        class="absolute left-1 right-1 rounded border p-1 text-xs cursor-pointer hover:shadow-md transition-shadow"
                                        :class="statusColors[booking.status]"
                                        :style="{
                                            top: getBookingTop(booking) + '%',
                                            height: getBookingHeight(booking) + '%',
                                            minHeight: '40px',
                                        }"
                                        @click="router.visit(route('bookings.show', booking.id))"
                                    >
                                        <div class="font-medium truncate">{{ booking.service?.name || 'Услуга' }}</div>
                                        <div class="text-xs opacity-75 truncate">{{ booking.customer?.name || 'Клиент' }}</div>
                                        <div class="text-xs opacity-75">{{ formatTime(booking.start_time) }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Список -->
            <Card v-else>
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
                            <TableRow v-for="booking in (bookings as any).data || []" :key="booking.id">
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
                            <TableRow v-if="(bookings as any).data?.length === 0">
                                <TableCell colspan="8" class="text-center py-8 text-muted-foreground">
                                    Нет бронирований
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>

                    <!-- Pagination -->
                    <div v-if="(bookings as any).last_page > 1" class="mt-4 flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Страница {{ (bookings as any).current_page }} из {{ (bookings as any).last_page }}
                        </div>
                        <div class="flex gap-2">
                            <Link
                                v-for="link in (bookings as any).links"
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

            <!-- Sheet для создания бронирования -->
            <Sheet :open="isCreateSheetOpen" @update:open="closeCreateSheet">
                <SheetContent side="right" class="overflow-y-auto">
                    <SheetHeader>
                        <SheetTitle>Новое бронирование</SheetTitle>
                        <SheetDescription>
                            Создайте новое бронирование для клиента
                        </SheetDescription>
                    </SheetHeader>

                    <div class="mt-6">
                        <BookingCreateForm
                            :services="props.services"
                            :staff="props.staff"
                            :on-success="handleSuccess"
                            :on-cancel="() => closeCreateSheet(false)"
                        />
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    </AppLayout>
</template>
