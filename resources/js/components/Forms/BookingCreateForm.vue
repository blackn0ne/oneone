<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { AcceptableValue } from 'reka-ui';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { SheetFooter } from '@/components/ui/sheet';
import BookingCalendar from '@/components/booking/BookingCalendar.vue';
import type { Service, Staff, Booking } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    services?: Service[];
    staff?: Staff[];
    booking?: Booking;
    workingHours?: {
        [key: string]: {
            is_closed: boolean;
            start: string;
            end: string;
        };
    };
    onSuccess?: () => void;
    onCancel?: () => void;
}

const props = defineProps<Props>();

const isEditMode = computed(() => !!props.booking);

const form = useForm({
    service_id: props.booking?.service_id ? String(props.booking.service_id) : '',
    staff_id: props.booking?.staff_id ? String(props.booking.staff_id) : '',
    customer_name: props.booking?.customer?.name || '',
    customer_phone: props.booking?.customer?.phone || '',
    business_id: props.booking?.business_id ? String(props.booking.business_id) : '',
    status: (props.booking?.status || 'pending') as 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show',
    start_time: props.booking?.start_time || '',
    end_time: props.booking?.end_time || '',
    duration: props.booking?.duration || null as number | null,
    participants_count: props.booking?.participants_count || 1,
    is_group: props.booking?.is_group || false,
    is_recurring: props.booking?.is_recurring || false,
    recurring_pattern: props.booking?.recurring_pattern || 'weekly',
    recurring_end_date: props.booking?.recurring_end_date || '',
    notes: props.booking?.notes || '',
});

// Инициализация даты и времени из существующего бронирования
const initializeDateTime = () => {
    if (props.booking?.start_time) {
        const startDate = new Date(props.booking.start_time);
        selectedDate.value = startDate;
        
        const hours = String(startDate.getHours()).padStart(2, '0');
        const minutes = String(startDate.getMinutes()).padStart(2, '0');
        selectedTime.value = `${hours}:${minutes}`;
        
        // Устанавливаем длительность если есть
        if (props.booking.duration && !form.duration) {
            form.duration = props.booking.duration;
        }
    }
};

const selectedDate = ref<Date>();
const selectedTime = ref('');
const selectedService = ref<Service | null>(null);

// Инициализация при монтировании
initializeDateTime();

// Инициализация услуги
if (props.booking?.service_id) {
    const service = (props.services || []).find(s => s.id === props.booking!.service_id);
    if (service) {
        selectedService.value = service;
        if (!form.duration) {
            form.duration = service.duration;
        }
    }
}

// Маппинг дней недели
const dayMap: Record<number, string> = {
    0: 'sunday',    // Воскресенье
    1: 'monday',    // Понедельник
    2: 'tuesday',   // Вторник
    3: 'wednesday', // Среда
    4: 'thursday',  // Четверг
    5: 'friday',    // Пятница
    6: 'saturday',  // Суббота
};

// Генерация временных слотов на основе рабочих часов и длительности услуги
const timeSlots = computed(() => {
    // Если нет выбранной даты или услуги, возвращаем пустой массив
    if (!selectedDate.value || !selectedService.value || !form.duration) {
        return [];
    }
    
    // Получаем день недели (0 = воскресенье, 1 = понедельник, и т.д.)
    const dayOfWeek = selectedDate.value.getDay();
    const dayKey = dayMap[dayOfWeek];
    
    // Получаем рабочие часы для выбранного дня
    if (!props.workingHours || !props.workingHours[dayKey]) {
        // Если нет рабочих часов, используем дефолтные 8:00-22:00
        return generateTimeSlots('08:00', '22:00', form.duration);
    }
    
    const dayHours = props.workingHours[dayKey];
    
    // Если день выходной, возвращаем пустой массив
    if (dayHours.is_closed) {
        return [];
    }
    
    // Генерируем слоты на основе рабочих часов и длительности
    return generateTimeSlots(dayHours.start, dayHours.end, form.duration);
});

// Функция генерации временных слотов
function generateTimeSlots(startTime: string, endTime: string, duration: number): string[] {
    const slots: string[] = [];
    
    // Парсим время начала и конца
    const [startHour, startMinute] = startTime.split(':').map(Number);
    const [endHour, endMinute] = endTime.split(':').map(Number);
    
    // Создаем Date объекты для удобной работы
    const start = new Date();
    start.setHours(startHour, startMinute, 0, 0);
    
    const end = new Date();
    end.setHours(endHour, endMinute, 0, 0);
    
    // Генерируем слоты с интервалом 30 минут
    const current = new Date(start);
    const slotDuration = duration; // длительность в минутах
    
    while (current < end) {
        // Проверяем, что слот + длительность не выходит за рабочие часы
        const slotEnd = new Date(current);
        slotEnd.setMinutes(slotEnd.getMinutes() + slotDuration);
        
        if (slotEnd <= end) {
            const hours = String(current.getHours()).padStart(2, '0');
            const minutes = String(current.getMinutes()).padStart(2, '0');
            slots.push(`${hours}:${minutes}`);
        }
        
        // Переходим к следующему слоту (каждые 30 минут)
        current.setMinutes(current.getMinutes() + 30);
    }
    
    return slots;
}

const onServiceChange = (serviceId: AcceptableValue) => {
    if (!serviceId || typeof serviceId !== 'string') {
        form.service_id = '';
        selectedService.value = null;
        form.duration = null;
        selectedTime.value = '';
        return;
    }
    form.service_id = serviceId;
    const service = (props.services || []).find(s => s.id === Number(serviceId));
    selectedService.value = service || null;
    if (service) {
        form.duration = service.duration;
        // Сбрасываем выбранное время при смене услуги
        selectedTime.value = '';
    }
};

const onDateChange = (date: Date | undefined) => {
    if (date) {
        selectedDate.value = date;
        updateDateTime();
    }
};

const onTimeChange = (time: AcceptableValue) => {
    if (!time || typeof time !== 'string') {
        selectedTime.value = '';
        return;
    }
    selectedTime.value = time;
    updateDateTime();
};

const updateDateTime = () => {
    if (selectedDate.value && selectedTime.value && form.duration) {
        const [hours, minutes] = selectedTime.value.split(':');
        const start = new Date(selectedDate.value);
        start.setHours(Number(hours), Number(minutes), 0, 0);
        
        const end = new Date(start);
        end.setMinutes(end.getMinutes() + form.duration);

        form.start_time = start.toISOString();
        form.end_time = end.toISOString();
    }
};

const submit = () => {
    if (isEditMode.value && props.booking) {
        // Режим редактирования
        form.put(route('bookings.update', props.booking.id), {
            preserveScroll: true,
            onSuccess: () => {
                props.onSuccess?.();
            },
        });
    } else {
        // Режим создания
        form.post(route('bookings.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                form.customer_name = '';
                form.customer_phone = '';
                selectedDate.value = undefined;
                selectedTime.value = '';
                selectedService.value = null;
                props.onSuccess?.();
            },
        });
    }
};
</script>

<template>
    
    
    <form @submit.prevent="submit" class="flex flex-col space-y-6 h-full">
        <!-- Основная информация -->
        <div class="px-4">
            <!-- Услуга и Сотрудник -->
            <div class="grid gap-4">
                <div class="space-y-2">
                    <Label for="service">Услуга *</Label>
                    <Select
                        id="service"
                        :model-value="form.service_id"
                        @update:model-value="onServiceChange"
                        required
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Выберите услугу" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="service in (services || [])"
                                :key="service.id"
                                :value="String(service.id)"
                            >
                                {{ service.name }} - {{ service.price }} ₸
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.service_id" class="text-sm text-destructive mt-1">
                        {{ form.errors.service_id }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="staff">Сотрудник</Label>
                    <Select
                        id="staff"
                        v-model="form.staff_id"
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Выберите сотрудника (опционально)" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="staffMember in (staff || [])"
                                :key="staffMember.id"
                                :value="String(staffMember.id)"
                            >
                                {{ staffMember.name }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                </div>
            
                <div class="space-y-2">
                    <Label for="customer_name">Имя клиента *</Label>
                    <Input
                        id="customer_name"
                        v-model="form.customer_name"
                        placeholder="Введите имя клиента"
                        class="w-full"
                        required
                    />
                    <p v-if="form.errors.customer_name" class="text-sm text-destructive mt-1">
                        {{ form.errors.customer_name }}
                    </p>
                </div>
                
                <div class="space-y-2">
                    <Label for="customer_phone">Телефон клиента *</Label>
                    <Input
                        id="customer_phone"
                        v-model="form.customer_phone"
                        type="tel"
                        placeholder="+7 (999) 123-45-67"
                        class="w-full"
                        required
                    />
                    <p v-if="form.errors.customer_phone" class="text-sm text-destructive mt-1">
                        {{ form.errors.customer_phone }}
                    </p>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-2">
                        <Label>Дата *</Label>
                        <div class="w-full">
                            <BookingCalendar :date="selectedDate" @update:date="onDateChange" />
                        </div>
                        <p v-if="form.errors.start_time" class="text-sm text-destructive mt-1">
                            {{ form.errors.start_time }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="time">Время *</Label>
                        <Select
                            id="time"
                            v-model="selectedTime"
                            @update:model-value="onTimeChange"
                            required
                        >
                            <SelectTrigger class="w-full">
                                <SelectValue placeholder="Выберите время" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="slot in timeSlots"
                                    :key="slot"
                                    :value="slot"
                                >
                                    {{ slot }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.start_time" class="text-sm text-destructive mt-1">
                            {{ form.errors.start_time }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="status">Статус *</Label>
                    <Select
                        id="status"
                        v-model="form.status"
                        required
                    >
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Выберите статус" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="pending">Ожидает</SelectItem>
                            <SelectItem value="confirmed">Подтверждено</SelectItem>
                            <SelectItem value="cancelled">Отменено</SelectItem>
                            <SelectItem value="completed">Завершено</SelectItem>
                            <SelectItem value="no_show">Не явился</SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="form.errors.status" class="text-sm text-destructive mt-1">
                        {{ form.errors.status }}
                    </p>
                </div>
            
                <div class="space-y-2">
                    <Label for="notes">Примечания</Label>
                    <Textarea
                        id="notes"
                        v-model="form.notes"
                        placeholder="Дополнительная информация..."
                        rows="3"
                        class="w-full resize-none"
                    />
                </div>
            </div>
        </div>

        <!-- Кнопки действий -->
        <SheetFooter class="border-t p-4 mt-auto">
            <Button type="submit" :disabled="form.processing" class="w-full">
                {{ form.processing 
                    ? (isEditMode ? 'Сохранение...' : 'Создание...') 
                    : (isEditMode ? 'Сохранить изменения' : 'Создать бронирование') 
                }}
            </Button>
        </SheetFooter>
    </form>
</template>
