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
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { PlusIcon, SearchIcon, Calendar as CalendarIcon, List, ChevronLeft, ChevronRight, Clock, User, Scissors, Edit, Trash2 } from 'lucide-vue-next';
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
    workingHours?: {
        [key: string]: {
            is_closed: boolean;
            start: string;
            end: string;
        };
    };
}

const props = defineProps<Props>();

const searchQuery = ref('');
const currentView = ref<'calendar' | 'list'>(props.view || 'calendar');
const isCreateSheetOpen = ref(false);
const isEditSheetOpen = ref(false);
const selectedBooking = ref<Booking | null>(null);
const isDeleteDialogOpen = ref(false);
const bookingToDelete = ref<Booking | null>(null);

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
    loadWeekBookings();
};

const openEditSheet = (booking: Booking) => {
    selectedBooking.value = booking;
    isEditSheetOpen.value = true;
};

const closeEditSheet = (open: boolean) => {
    if (!open) {
        isEditSheetOpen.value = false;
        selectedBooking.value = null;
    }
};

const handleEditSuccess = () => {
    isEditSheetOpen.value = false;
    selectedBooking.value = null;
    loadWeekBookings();
};

// Для календарного вида
const currentWeekStart = ref(props.startDate ? new Date(props.startDate) : getStartOfWeek(new Date()));
const weekDays = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];

// Вычисляем диапазон часов на основе рабочих часов
const hours = computed(() => {
    if (!props.workingHours) {
        // Если нет рабочих часов, используем стандартный диапазон 8:00-22:00
        return Array.from({ length: 15 }, (_, i) => i + 8);
    }
    
    // Находим минимальное и максимальное время из всех дней недели
    let minHour = 24;
    let maxHour = 0;
    
    const dayMap: Record<string, string> = {
        'monday': 'Пн',
        'tuesday': 'Вт',
        'wednesday': 'Ср',
        'thursday': 'Чт',
        'friday': 'Пт',
        'saturday': 'Сб',
        'sunday': 'Вс',
    };
    
    Object.keys(props.workingHours).forEach(dayKey => {
        const dayData = props.workingHours![dayKey];
        if (!dayData.is_closed && dayData.start && dayData.end) {
            const startHour = parseInt(dayData.start.split(':')[0]);
            const endHour = parseInt(dayData.end.split(':')[0]);
            
            if (startHour < minHour) minHour = startHour;
            if (endHour > maxHour) maxHour = endHour;
        }
    });
    
    // Если не нашли рабочие часы, используем стандартный диапазон
    if (minHour === 24 || maxHour === 0) {
        return Array.from({ length: 15 }, (_, i) => i + 8);
    }
    
    // Создаем массив часов от минимального до максимального
    return Array.from({ length: maxHour - minHour + 1 }, (_, i) => i + minHour);
});

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
    let bookings: Booking[] = [];
    if (Array.isArray(props.bookings)) {
        bookings = props.bookings;
    } else {
        bookings = props.bookings?.data || [];
    }
    // Фильтруем отмененные и no_show из календаря
    return bookings.filter((booking: Booking) => 
        booking.status !== 'cancelled' && booking.status !== 'no_show'
    );
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
    
    // Сортируем бронирования по времени начала и сотруднику для правильного отображения
    Object.keys(grouped).forEach(dateKey => {
        Object.keys(grouped[dateKey]).forEach(hourKey => {
            grouped[dateKey][hourKey].sort((a, b) => {
                const timeDiff = new Date(a.start_time).getTime() - new Date(b.start_time).getTime();
                if (timeDiff !== 0) return timeDiff;
                // Если время одинаковое, сортируем по ID сотрудника
                const staffA = a.staff?.id || 0;
                const staffB = b.staff?.id || 0;
                return staffA - staffB;
            });
        });
    });
    
    return grouped;
});

// Функция для определения позиции карточки (для множественных записей на одно время)
const getBookingPosition = (booking: Booking, bookingIndex: number, allBookings: Booking[]) => {
    const bookingStart = new Date(booking.start_time).getTime();
    const bookingEnd = new Date(booking.end_time).getTime();
    
    // Находим все записи, которые перекрываются по времени с текущей
    const overlappingIndices: number[] = [];
    allBookings.forEach((b, idx) => {
        if (idx === bookingIndex) return;
        const bStart = new Date(b.start_time).getTime();
        const bEnd = new Date(b.end_time).getTime();
        // Проверяем перекрытие: начало одной записи меньше конца другой и наоборот
        if (bStart < bookingEnd && bEnd > bookingStart) {
            overlappingIndices.push(idx);
        }
    });
    
    // Если есть перекрывающиеся записи, располагаем их рядом
    if (overlappingIndices.length > 0) {
        // Создаем группу перекрывающихся записей (включая текущую)
        const group = [bookingIndex, ...overlappingIndices].sort((a, b) => a - b);
        const positionInGroup = group.indexOf(bookingIndex);
        const totalInGroup = group.length;
        const width = 100 / totalInGroup;
        const left = positionInGroup * width;
        
        return {
            left: `${left}%`,
            width: `${width}%`,
            zIndex: bookingIndex + 1,
        };
    }
    
    // Если нет перекрытий, используем полную ширину
    return {
        left: '2px',
        right: '2px',
        width: 'calc(100% - 4px)',
        zIndex: bookingIndex + 1,
    };
};

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

type BookingStatus = 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show';

const statusLabels: Record<BookingStatus, string> = {
    pending: 'Ожидает',
    confirmed: 'Подтверждено',
    cancelled: 'Отменено',
    completed: 'Завершено',
    no_show: 'Не явился',
};

const statusVariants: Record<BookingStatus, 'secondary' | 'default' | 'destructive'> = {
    pending: 'secondary',
    confirmed: 'default',
    cancelled: 'destructive',
    completed: 'default',
    no_show: 'destructive',
};

const statusColors = {
    pending: 'bg-yellow-50 border-l-yellow-400 text-yellow-900 hover:bg-yellow-100',
    confirmed: 'bg-blue-50 border-l-blue-400 text-blue-900 hover:bg-blue-100',
    cancelled: 'bg-red-50 border-l-red-400 text-red-900 hover:bg-red-100',
    completed: 'bg-green-50 border-l-green-400 text-green-900 hover:bg-green-100',
    no_show: 'bg-gray-50 border-l-gray-400 text-gray-900 hover:bg-gray-100',
};

const openDeleteDialog = (booking: Booking) => {
    bookingToDelete.value = booking;
    isDeleteDialogOpen.value = true;
};

const closeDeleteDialog = () => {
    isDeleteDialogOpen.value = false;
    bookingToDelete.value = null;
};

const confirmDelete = () => {
    if (bookingToDelete.value) {
        router.delete(route('bookings.destroy', bookingToDelete.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                closeDeleteDialog();
            },
        });
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
                                    class="relative border-l border-b min-h-[60px] p-0.5"
                                    :class="day.toDateString() === new Date().toDateString() ? 'bg-blue-50/30' : ''"
                                >
                                    <template
                                        v-for="(booking, bookingIndex) in (bookingsByDateAndTime[formatDateKey(day)]?.[String(hour)] || [])"
                                        :key="booking.id"
                                    >
                                        <div
                                            class="absolute rounded-md border-l-2 p-1.5 text-[10px] cursor-pointer hover:shadow-md hover:z-50 transition-all overflow-hidden"
                                            :class="statusColors[booking.status]"
                                            :style="{
                                                top: getBookingTop(booking) + '%',
                                                height: getBookingHeight(booking) + '%',
                                                minHeight: '36px',
                                                maxHeight: '100%',
                                                ...getBookingPosition(booking, bookingIndex, bookingsByDateAndTime[formatDateKey(day)]?.[String(hour)] || []),
                                            }"
                                            @click="openEditSheet(booking)"
                                        >
                                            <div class="flex items-start gap-1.5 h-full">
                                                <div class="flex-shrink-0 mt-0.5">
                                                    <Scissors class="h-2.5 w-2.5 opacity-70" />
                                                </div>
                                                <div class="flex-1 min-w-0 space-y-0.5">
                                                    <div class="font-semibold leading-tight truncate">
                                                        {{ booking.service?.name || 'Услуга' }}
                                                    </div>
                                                    <div class="flex items-center gap-1 text-[9px] opacity-80">
                                                        <User class="h-2 w-2 flex-shrink-0" />
                                                        <span class="truncate">{{ booking.staff?.name || 'Без мастера' }}</span>
                                                    </div>
                                                    <div class="flex items-center gap-1 text-[9px] opacity-70">
                                                        <Clock class="h-2 w-2 flex-shrink-0" />
                                                        <span>{{ formatTime(booking.start_time) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
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
                                    <Badge :variant="statusVariants[booking.status as BookingStatus] || 'secondary'">
                                        {{ statusLabels[booking.status as BookingStatus] || booking.status }}
                                    </Badge>
                                </TableCell>
                                <TableCell>
                                    {{ booking.total_price?.toLocaleString('ru-RU') }} ₸
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openEditSheet(booking)"
                                            class="h-8 w-8 p-0"
                                        >
                                            <Edit class="h-4 w-4" />
                                        </Button>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="openDeleteDialog(booking)"
                                            class="h-8 w-8 p-0 text-destructive hover:text-destructive"
                                        >
                                            <Trash2 class="h-4 w-4" />
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
                <SheetContent side="right" class="flex flex-col p-0">
                    <SheetHeader class="border-b p-4">
                        <SheetTitle><h4 class="text-lg font-semibold">Новая бронь</h4></SheetTitle>
                    </SheetHeader>

                    <div class="flex-1 overflow-y-auto">
                        <BookingCreateForm
                            :services="props.services"
                            :staff="props.staff"
                            :working-hours="props.workingHours"
                            :on-success="handleSuccess"
                            :on-cancel="() => closeCreateSheet(false)"
                        />
                    </div>
                </SheetContent>
            </Sheet>

            <!-- Sheet для редактирования бронирования -->
            <Sheet :open="isEditSheetOpen" @update:open="closeEditSheet">
                <SheetContent side="right" class="flex flex-col p-0">
                    <SheetHeader class="border-b p-4">
                        <SheetTitle><h4 class="text-lg font-semibold">Редактировать бронирование</h4></SheetTitle>
                        <SheetDescription v-if="selectedBooking">
                            {{ selectedBooking.service?.name }} - {{ formatTime(selectedBooking.start_time) }}
                        </SheetDescription>
                    </SheetHeader>

                    <div class="flex-1 overflow-y-auto">
                        <BookingCreateForm
                            v-if="selectedBooking"
                            :booking="selectedBooking"
                            :services="props.services"
                            :staff="props.staff"
                            :working-hours="props.workingHours"
                            :on-success="handleEditSuccess"
                            :on-cancel="() => closeEditSheet(false)"
                        />
                    </div>
                </SheetContent>
            </Sheet>

            <!-- Dialog для подтверждения удаления -->
            <Dialog :open="isDeleteDialogOpen" @update:open="(open) => !open && closeDeleteDialog()">
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Подтверждение удаления</DialogTitle>
                        <DialogDescription>
                            Вы уверены, что хотите удалить бронирование
                            <span v-if="bookingToDelete" class="font-semibold">
                                #{{ bookingToDelete.booking_number }}
                            </span>?
                            Это действие нельзя отменить.
                        </DialogDescription>
                    </DialogHeader>
                    <DialogFooter>
                        <Button variant="outline" @click="closeDeleteDialog">
                            Отмена
                        </Button>
                        <Button variant="destructive" @click="confirmDelete">
                            Удалить
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
        </div>
    </AppLayout>
</template>
