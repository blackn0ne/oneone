<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import BookingCalendar from './BookingCalendar.vue';
import { toast } from 'vue-sonner';

interface Service {
    id: string | number;
    name: string;
    duration: number;
    price: number;
}

interface Staff {
    id: string | number;
    name: string;
}

interface Props {
    services?: Service[];
    staff?: Staff[];
}

const props = withDefaults(defineProps<Props>(), {
    services: () => [],
    staff: () => [],
});

const form = useForm({
    service_id: '',
    staff_id: '',
    customer_name: '',
    customer_email: '',
    customer_phone: '',
    date: null as Date | null,
    time: '',
    notes: '',
    is_recurring: false,
    recurring_pattern: 'weekly',
});

const emit = defineEmits<{
    submit: [data: typeof form.data];
}>();

const submit = () => {
    emit('submit', form.data);
    // Или используйте напрямую:
    // form.post('/bookings', {
    //     preserveScroll: true,
    //     onSuccess: () => {
    //         toast.success('Бронирование успешно создано!');
    //         form.reset();
    //     },
    //     onError: () => {
    //         toast.error('Ошибка при создании бронирования');
    //     },
    // });
};

const timeSlots = [
    '09:00', '09:30', '10:00', '10:30', '11:00', '11:30',
    '12:00', '12:30', '13:00', '13:30', '14:00', '14:30',
    '15:00', '15:30', '16:00', '16:30', '17:00', '17:30',
    '18:00', '18:30', '19:00', '19:30', '20:00',
];
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Новое бронирование</CardTitle>
            <CardDescription>
                Заполните форму для создания нового бронирования
            </CardDescription>
        </CardHeader>
        <form @submit.prevent="submit">
            <CardContent class="space-y-4">
                <!-- Услуга -->
                <div class="space-y-2">
                    <Label for="service">Услуга *</Label>
                    <Select v-model="form.service_id" required>
                        <SelectTrigger id="service">
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
                    <Select v-model="form.staff_id">
                        <SelectTrigger id="staff">
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

                <!-- Дата -->
                <div class="space-y-2">
                    <Label>Дата *</Label>
                    <BookingCalendar v-model:date="form.date" />
                    <p v-if="form.errors.date" class="text-sm text-destructive">
                        {{ form.errors.date }}
                    </p>
                </div>

                <!-- Время -->
                <div class="space-y-2">
                    <Label for="time">Время *</Label>
                    <Select v-model="form.time" required>
                        <SelectTrigger id="time">
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
                    <p v-if="form.errors.time" class="text-sm text-destructive">
                        {{ form.errors.time }}
                    </p>
                </div>

                <!-- Данные клиента -->
                <div class="grid gap-4 md:grid-cols-2">
                    <div class="space-y-2">
                        <Label for="customer_name">Имя клиента *</Label>
                        <Input
                            id="customer_name"
                            v-model="form.customer_name"
                            placeholder="Иван Иванов"
                            required
                        />
                        <p v-if="form.errors.customer_name" class="text-sm text-destructive">
                            {{ form.errors.customer_name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="customer_email">Email</Label>
                        <Input
                            id="customer_email"
                            v-model="form.customer_email"
                            type="email"
                            placeholder="ivan@example.com"
                        />
                        <p v-if="form.errors.customer_email" class="text-sm text-destructive">
                            {{ form.errors.customer_email }}
                        </p>
                    </div>
                </div>

                <div class="space-y-2">
                    <Label for="customer_phone">Телефон</Label>
                    <Input
                        id="customer_phone"
                        v-model="form.customer_phone"
                        type="tel"
                        placeholder="+7 (999) 123-45-67"
                    />
                    <p v-if="form.errors.customer_phone" class="text-sm text-destructive">
                        {{ form.errors.customer_phone }}
                    </p>
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

                <!-- Повторяющееся бронирование -->
                <div class="flex items-center space-x-2">
                    <Checkbox id="recurring" v-model="form.is_recurring" />
                    <Label for="recurring" class="cursor-pointer">
                        Повторяющееся бронирование
                    </Label>
                </div>

                <div v-if="form.is_recurring" class="space-y-2">
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
            </CardContent>
            <CardFooter class="flex justify-end gap-2">
                <Button type="button" variant="outline" @click="$emit('cancel')">
                    Отмена
                </Button>
                <Button type="submit" :disabled="form.processing">
                    {{ form.processing ? 'Создание...' : 'Создать бронирование' }}
                </Button>
            </CardFooter>
        </form>
    </Card>
</template>
