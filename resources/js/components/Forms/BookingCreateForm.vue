<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import type { AcceptableValue } from 'reka-ui';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { SheetFooter, SheetHeader, SheetTitle, SheetDescription } from '@/components/ui/sheet';
import BookingCalendar from '@/components/booking/BookingCalendar.vue';
import type { Service, Staff } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    services?: Service[];
    staff?: Staff[];
    onSuccess?: () => void;
    onCancel?: () => void;
}

const props = defineProps<Props>();

const form = useForm({
    service_id: '',
    staff_id: '',
    customer_name: '',
    customer_phone: '',
    business_id: '',
    start_time: '',
    end_time: '',
    duration: null as number | null,
    participants_count: 1,
    is_group: false,
    is_recurring: false,
    recurring_pattern: 'weekly',
    recurring_end_date: '',
    notes: '',
});

const selectedDate = ref<Date>();
const selectedTime = ref('');
const selectedService = ref<Service | null>(null);

const timeSlots = [
    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
    '18:00', '18:30', '19:00', '19:30', '20:00',
];

const onServiceChange = (serviceId: AcceptableValue) => {
    if (!serviceId || typeof serviceId !== 'string') {
        form.service_id = '';
        selectedService.value = null;
        return;
    }
    form.service_id = serviceId;
    const service = (props.services || []).find(s => s.id === Number(serviceId));
    selectedService.value = service || null;
    if (service && !form.duration) {
        form.duration = service.duration;
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
};
</script>

<template>
    <SheetHeader>
        <SheetTitle>Создать бронирование</SheetTitle>
        <SheetDescription>
            Заполните форму для создания нового бронирования
        </SheetDescription>
    </SheetHeader>
    
    <form @submit.prevent="submit" class="space-y-6">
        <!-- Основная информация -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold">Основная информация</h3>
            
            <!-- Услуга и Сотрудник -->
            <div class="grid gap-4 md:grid-cols-2">
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
                                {{ service.name }} - {{ service.price }} ₽
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
            </div>
        </div>

        <!-- Информация о клиенте -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold">Информация о клиенте</h3>
            
            <div class="grid gap-4 md:grid-cols-2">
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
            </div>
        </div>

        <!-- Дата и время -->
        <div class="space-y-4">
            <h3 class="text-lg font-semibold">Дата и время</h3>
            
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label>Дата *</Label>
                    <div class="w-full">
                        <BookingCalendar v-model:date="selectedDate" @update:date="onDateChange" />
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
        </div>

       
        <!-- Примечания -->
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

        <!-- Кнопки действий -->
        <SheetFooter>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Создание...' : 'Создать бронирование' }}
            </Button>
        </SheetFooter>
    </form>
</template>
