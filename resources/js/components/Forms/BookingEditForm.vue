<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { SheetFooter } from '@/components/ui/sheet';
import type { Booking } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    booking: Booking;
    onSuccess?: () => void;
    onCancel?: () => void;
}

const props = defineProps<Props>();

const form = useForm({
    status: props.booking.status as 'pending' | 'confirmed' | 'cancelled' | 'completed' | 'no_show',
    notes: props.booking.notes || '',
});

const formatTime = (date: string | Date) => {
    const d = date instanceof Date ? date : new Date(date);
    return d.toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit',
    });
};

const submit = () => {
    form.put(route('bookings.update', props.booking.id), {
        preserveScroll: true,
        onSuccess: () => {
            props.onSuccess?.();
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col space-y-6 h-full">
        <div class="px-4 space-y-4">
            <!-- Информация о бронировании -->
            <div class="space-y-2 p-4 bg-muted rounded-lg">
                <div class="text-sm text-muted-foreground">Услуга</div>
                <div class="font-medium">{{ booking.service?.name || 'Услуга' }}</div>
            </div>

            <div class="space-y-2 p-4 bg-muted rounded-lg">
                <div class="text-sm text-muted-foreground">Мастер</div>
                <div class="font-medium">{{ booking.staff?.name || 'Без мастера' }}</div>
            </div>

            <div class="space-y-2 p-4 bg-muted rounded-lg">
                <div class="text-sm text-muted-foreground">Клиент</div>
                <div class="font-medium">{{ booking.customer?.name || 'Клиент' }}</div>
            </div>

            <div class="space-y-2 p-4 bg-muted rounded-lg" v-if="booking.customer?.phone">
                <div class="text-sm text-muted-foreground">Телефон клиента</div>
                <div class="font-medium">{{ booking.customer.phone }}</div>
            </div>

            <div class="space-y-2 p-4 bg-muted rounded-lg">
                <div class="text-sm text-muted-foreground">Время</div>
                <div class="font-medium">
                    {{ formatTime(booking.start_time) }} - {{ formatTime(booking.end_time) }}
                </div>
            </div>

            <!-- Статус -->
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
                        <SelectItem value="completed">Завершено</SelectItem>
                        <SelectItem value="cancelled">Отменено</SelectItem>
                        <SelectItem value="no_show">Не явился</SelectItem>
                    </SelectContent>
                </Select>
                <p v-if="form.errors.status" class="text-sm text-destructive mt-1">
                    {{ form.errors.status }}
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
                    class="w-full resize-none"
                />
            </div>
        </div>

        <!-- Кнопки действий -->
        <SheetFooter class="border-t p-4 mt-auto">
            <Button type="submit" :disabled="form.processing" class="w-full">
                {{ form.processing ? 'Сохранение...' : 'Сохранить изменения' }}
            </Button>
        </SheetFooter>
    </form>
</template>
