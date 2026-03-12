<script setup lang="ts">
import { computed } from 'vue';
import type { DateValue } from 'reka-ui';
import { CalendarDate, getLocalTimeZone } from '@internationalized/date';
import { toDate } from 'reka-ui/date';
import { Calendar } from '@/components/ui/calendar';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { CalendarIcon } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

interface Props {
    date?: Date;
}

const props = defineProps<Props>();

const emit = defineEmits<{
    'update:date': [date: Date | undefined];
}>();

// Конвертация Date в DateValue для Calendar
const calendarDate = computed<DateValue | undefined>({
    get: () => {
        if (!props.date || !(props.date instanceof Date) || isNaN(props.date.getTime())) {
            return undefined;
        }
        try {
            const timeZone = getLocalTimeZone();
            // Создаем CalendarDate напрямую из Date
            return new CalendarDate(
                props.date.getFullYear(),
                props.date.getMonth() + 1, // CalendarDate использует 1-based месяцы
                props.date.getDate()
            );
        } catch (error) {
            console.error('Error converting date to CalendarDate:', error);
            return undefined;
        }
    },
    set: (value: DateValue | undefined) => {
        if (!value) {
            emit('update:date', undefined);
            return;
        }
        try {
            // Конвертация DateValue обратно в Date
            const timeZone = getLocalTimeZone();
            const date = toDate(value, timeZone);
            emit('update:date', date);
        } catch (error) {
            console.error('Error converting DateValue to Date:', error);
        }
    },
});

// Для отображения в кнопке
const displayDate = computed(() => props.date);
</script>

<template>
    <div class="relative">
        <Popover>
            <PopoverTrigger as-child>
                <Button
                    variant="outline"
                    :class="cn(
                        'w-full justify-start text-left font-normal',
                        !displayDate && 'text-muted-foreground'
                    )"
                >
                    <CalendarIcon class="mr-2 h-4 w-4" />
                    <span v-if="displayDate && displayDate instanceof Date && !isNaN(displayDate.getTime())">
                        {{ displayDate.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                    </span>
                    <span v-else>Выберите дату</span>
                </Button>
            </PopoverTrigger>
            <PopoverContent class="w-auto p-0 relative" align="start">
                <Calendar
                    v-model="calendarDate"
                />
            </PopoverContent>
        </Popover>
    </div>
</template>
