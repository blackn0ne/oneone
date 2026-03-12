<script setup lang="ts">
import { ref } from 'vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetFooter,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import BookingCalendar from '@/components/booking/BookingCalendar.vue';
import type { Service, Staff, Customer } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    services: Service[];
    staff: Staff[];
    customers: Customer[];
}

const props = defineProps<Props>();

const form = useForm({
    service_id: '',
    staff_id: '',
    customer_id: '',
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

const isOpen = ref(true);
const selectedDate = ref<Date>();
const selectedTime = ref('');

const timeSlots = [
    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
    '18:00', '18:30', '19:00', '19:30', '20:00',
];

const selectedService = ref<Service | null>(null);

const onServiceChange = (serviceId: string) => {
    form.service_id = serviceId;
    const service = props.services.find(s => s.id === Number(serviceId));
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

const onTimeChange = (time: string) => {
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
            isOpen.value = false;
            router.visit(route('bookings.index'));
        },
    });
};

const handleClose = (open: boolean) => {
    if (!open) {
        isOpen.value = false;
        router.visit(route('bookings.index'));
    }
};
</script>

<template>
    <Head title="Новое бронирование" />

    <AppLayout>
        <Sheet :open="isOpen" @update:open="(open) => handleClose(open)">
            <SheetContent side="right" class="overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Новое бронирование</SheetTitle>
                    <SheetDescription>
                        Создайте новое бронирование для клиента
                    </SheetDescription>
                </SheetHeader>

                <form @submit.prevent="submit" class="space-y-4 mt-6">
                    <!-- Услуга -->
                    <div class="space-y-2">
                        <Label for="service">Услуга *</Label>
                        <Select
                            id="service"
                            :model-value="form.service_id"
                            @update:model-value="onServiceChange"
                            required
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Выберите услугу" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="service in services"
                                    :key="service.id"
                                    :value="String(service.id)"
                                >
                                    {{ service.name }} - {{ service.price }} ₸
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.service_id" class="text-sm text-destructive">
                            {{ form.errors.service_id }}
                        </p>
                    </div>

                    <!-- Сотрудник -->
                    <div class="space-y-2">
                        <Label for="staff">Сотрудник</Label>
                        <Select
                            id="staff"
                            v-model="form.staff_id"
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Выберите сотрудника (опционально)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="staffMember in staff"
                                    :key="staffMember.id"
                                    :value="String(staffMember.id)"
                                >
                                    {{ staffMember.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- Клиент -->
                    <div class="space-y-2">
                        <Label for="customer">Клиент *</Label>
                        <Select
                            id="customer"
                            v-model="form.customer_id"
                            required
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Выберите клиента" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="customer in customers"
                                    :key="customer.id"
                                    :value="String(customer.id)"
                                >
                                    {{ customer.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.customer_id" class="text-sm text-destructive">
                            {{ form.errors.customer_id }}
                        </p>
                    </div>

                    <!-- Дата -->
                    <div class="space-y-2">
                        <Label>Дата *</Label>
                        <BookingCalendar v-model:date="selectedDate" @update:date="onDateChange" />
                        <p v-if="form.errors.start_time" class="text-sm text-destructive">
                            {{ form.errors.start_time }}
                        </p>
                    </div>

                    <!-- Время -->
                    <div class="space-y-2">
                        <Label for="time">Время *</Label>
                        <Select
                            id="time"
                            v-model="selectedTime"
                            @update:model-value="onTimeChange"
                            required
                        >
                            <SelectTrigger>
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
                        <p v-if="form.errors.start_time" class="text-sm text-destructive">
                            {{ form.errors.start_time }}
                        </p>
                    </div>


                    <!-- Повторяющееся бронирование -->
                    <div class="space-y-2">
                        <div class="flex items-center space-x-2">
                            <Checkbox id="recurring" v-model="form.is_recurring" />
                            <Label for="recurring" class="cursor-pointer">
                                Повторяющееся бронирование
                            </Label>
                        </div>
                        <div v-if="form.is_recurring" class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="recurring_pattern">Периодичность</Label>
                                <Select v-model="form.recurring_pattern">
                                    <SelectTrigger id="recurring_pattern">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="daily">Ежедневно</SelectItem>
                                        <SelectItem value="weekly">Еженедельно</SelectItem>
                                        <SelectItem value="monthly">Ежемесячно</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                            <div class="space-y-2">
                                <Label for="recurring_end_date">Дата окончания</Label>
                                <Input
                                    id="recurring_end_date"
                                    v-model="form.recurring_end_date"
                                    type="date"
                                />
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
                        />
                    </div>

                    <SheetFooter class="gap-2 sm:gap-0">
                        <Button type="button" variant="outline" @click="handleClose">
                            Отмена
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Создание...' : 'Создать бронирование' }}
                        </Button>
                    </SheetFooter>
                </form>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
