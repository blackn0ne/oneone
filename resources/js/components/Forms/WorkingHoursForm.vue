<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

/**
 * Интерфейс для рабочих часов одного дня
 */
interface DayWorkingHours {
    is_closed: boolean;
    start: string;
    end: string;
}

/**
 * Интерфейс для всех рабочих часов (ключ - день недели)
 */
interface WorkingHours {
    [key: string]: DayWorkingHours;
}

interface Props {
    modelValue?: WorkingHours;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    'update:modelValue': [value: WorkingHours];
}>();

/**
 * Конфигурация дней недели
 */
const WEEK_DAYS = [
    { key: 'monday', label: 'Понедельник' },
    { key: 'tuesday', label: 'Вторник' },
    { key: 'wednesday', label: 'Среда' },
    { key: 'thursday', label: 'Четверг' },
    { key: 'friday', label: 'Пятница' },
    { key: 'saturday', label: 'Суббота' },
    { key: 'sunday', label: 'Воскресенье' },
] as const;

/**
 * Значения по умолчанию для рабочих часов
 */
const DEFAULT_HOURS: WorkingHours = {
    monday: { is_closed: false, start: '08:00', end: '22:00' },
    tuesday: { is_closed: false, start: '08:00', end: '22:00' },
    wednesday: { is_closed: false, start: '08:00', end: '22:00' },
    thursday: { is_closed: false, start: '08:00', end: '22:00' },
    friday: { is_closed: false, start: '08:00', end: '22:00' },
    saturday: { is_closed: false, start: '08:00', end: '22:00' },
    sunday: { is_closed: false, start: '08:00', end: '22:00' },
};

/**
 * Computed для рабочих часов с дефолтными значениями
 */
const workingHours = computed({
    get: () => {
        const merged = { ...DEFAULT_HOURS };
        const modelValue = props.modelValue;
        if (modelValue) {
            Object.keys(modelValue).forEach(day => {
                const dayData = modelValue[day];
                if (dayData) {
                    merged[day] = {
                        is_closed: dayData.is_closed ?? DEFAULT_HOURS[day].is_closed,
                        start: dayData.start || DEFAULT_HOURS[day].start,
                        end: dayData.end || DEFAULT_HOURS[day].end,
                    };
                }
            });
        }
        return merged;
    },
    set: (value: WorkingHours) => {
        emit('update:modelValue', value);
    },
});

/**
 * Обновить данные для конкретного дня
 */
const updateDay = (dayKey: string, field: keyof DayWorkingHours, value: boolean | string): void => {
    const updated = { ...workingHours.value };
    updated[dayKey] = {
        ...updated[dayKey],
        [field]: value,
    };
    workingHours.value = updated;
};

/**
 * Получить значение времени для поля
 */
const getTimeValue = (dayKey: string, field: 'start' | 'end'): string => {
    const dayData = workingHours.value[dayKey];
    if (!dayData) {
        return DEFAULT_HOURS[dayKey][field];
    }
    
    const value = dayData[field];
    if (!value) {
        return DEFAULT_HOURS[dayKey][field];
    }
    
    // Убеждаемся, что формат правильный (HH:MM) для input type="time"
    // Если пришло в формате HH:MM:SS, обрезаем до HH:MM
    const timeStr = String(value);
    return timeStr.length > 5 ? timeStr.substring(0, 5) : timeStr;
};

/**
 * Computed для состояний чекбоксов каждого дня
 * Каждый computed связан с workingHours и автоматически синхронизируется
 */
const dayCheckedStates = WEEK_DAYS.reduce((acc, day) => {
    acc[day.key] = computed({
        get: () => {
            return !workingHours.value[day.key]?.is_closed;
        },
        set: (value: boolean) => {
            updateDay(day.key, 'is_closed', !value);
        },
    });
    return acc;
}, {} as Record<string, ReturnType<typeof computed<boolean>>>);
</script>

<template>
    <div class="space-y-4">
        <div>
            <h3 class="text-lg font-semibold mb-4">Рабочие часы</h3>
            <div class="space-y-3">
                <div
                    v-for="day in WEEK_DAYS"
                    :key="day.key"
                    class="flex items-center gap-4 p-3 border rounded-lg"
                >
                    <div class="flex items-center space-x-2 min-w-[140px]">
                        <Checkbox
                            :id="`day-${day.key}`"
                            v-model="dayCheckedStates[day.key].value"
                        />
                        <Label :for="`day-${day.key}`" class="cursor-pointer font-medium">
                            {{ day.label }}
                        </Label>
                    </div>

                    <div
                        v-if="!workingHours[day.key]?.is_closed"
                        class="flex items-center gap-2 flex-1"
                    >
                        <div class="flex items-center gap-2">
                            <Label :for="`start-${day.key}`" class="text-sm text-muted-foreground">
                                С:
                            </Label>
                            <Input
                                :id="`start-${day.key}`"
                                type="time"
                                :model-value="getTimeValue(day.key, 'start')"
                                @update:model-value="updateDay(day.key, 'start', String($event))"
                                class="w-32"
                            />
                        </div>

                        <div class="flex items-center gap-2">
                            <Label :for="`end-${day.key}`" class="text-sm text-muted-foreground">
                                До:
                            </Label>
                            <Input
                                :id="`end-${day.key}`"
                                type="time"
                                :model-value="getTimeValue(day.key, 'end')"
                                @update:model-value="updateDay(day.key, 'end', String($event))"
                                class="w-32"
                            />
                        </div>
                    </div>

                    <div v-else class="text-sm text-muted-foreground">
                        Выходной
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
