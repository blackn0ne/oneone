<script setup lang="ts">
import { computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import type { Business } from '@/types';
import { route } from '@/lib/routes';

interface WorkingHours {
    is_closed?: boolean;
    start?: string;
    end?: string;
}

interface WorkingHoursData {
    monday?: WorkingHours;
    tuesday?: WorkingHours;
    wednesday?: WorkingHours;
    thursday?: WorkingHours;
    friday?: WorkingHours;
    saturday?: WorkingHours;
    sunday?: WorkingHours;
}

interface Props {
    business: Business;
}

const props = defineProps<Props>();

const weekDays = [
    { key: 'monday', label: 'Понедельник' },
    { key: 'tuesday', label: 'Вторник' },
    { key: 'wednesday', label: 'Среда' },
    { key: 'thursday', label: 'Четверг' },
    { key: 'friday', label: 'Пятница' },
    { key: 'saturday', label: 'Суббота' },
    { key: 'sunday', label: 'Воскресенье' },
];

const getDefaultWorkingHours = (): WorkingHoursData => {
    const defaultHours: WorkingHoursData = {};
    weekDays.forEach(day => {
        defaultHours[day.key as keyof WorkingHoursData] = {
            is_closed: false,
            start: '08:00',
            end: '22:00',
        };
    });
    return defaultHours;
};

const getWorkingHours = (): WorkingHoursData => {
    if (props.business?.working_hours) {
        const wh = props.business.working_hours;
        
        if (typeof wh === 'string') {
            try {
                const parsed = JSON.parse(wh);
                return parsed && typeof parsed === 'object' ? parsed : getDefaultWorkingHours();
            } catch {
                return getDefaultWorkingHours();
            }
        }
        if (wh && typeof wh === 'object' && Object.keys(wh).length > 0) {
            return wh as WorkingHoursData;
        }
    }
    return getDefaultWorkingHours();
};

const form = useForm({
    name: props.business.name || '',
    address: props.business.address || '',
    phone: props.business.phone || '',
    email: props.business.email || '',
    is_active: props.business.is_active ?? true,
    working_hours: getWorkingHours(),
});

// Computed для получения working_hours с гарантией наличия всех дней
const formWorkingHours = computed(() => {
    const hours = form.working_hours as WorkingHoursData | undefined;
    const result: WorkingHoursData = {};
    
    weekDays.forEach(day => {
        const dayData = hours?.[day.key as keyof WorkingHoursData];
        
        if (dayData && typeof dayData === 'object' && 'is_closed' in dayData) {
            result[day.key as keyof WorkingHoursData] = {
                is_closed: dayData.is_closed ?? false,
                start: dayData.start || '08:00',
                end: dayData.end || '22:00',
            };
        } else {
            result[day.key as keyof WorkingHoursData] = {
                is_closed: false,
                start: '08:00',
                end: '22:00',
            };
        }
    });
    
    return result;
});

// Computed для checked состояния каждого дня
const dayCheckedComputed: Record<string, ReturnType<typeof computed<boolean>>> = {};
// Computed для данных каждого дня
const dayDataComputed: Record<string, ReturnType<typeof computed<WorkingHours>>> = {};

weekDays.forEach(day => {
    dayCheckedComputed[day.key] = computed(() => {
        const dayData = formWorkingHours.value[day.key as keyof WorkingHoursData];
        return !(dayData?.is_closed ?? false);
    });
    
    dayDataComputed[day.key] = computed(() => {
        return formWorkingHours.value[day.key as keyof WorkingHoursData] || {
            is_closed: false,
            start: '08:00',
            end: '22:00',
        };
    });
});

// Функция для обновления checked состояния
const toggleDayChecked = (dayKey: string, checked: boolean) => {
    updateWorkingHours(dayKey, 'is_closed', !checked);
};



const updateWorkingHours = (day: string, field: 'is_closed' | 'start' | 'end', value: boolean | string) => {
    // Создаем новый объект working_hours для реактивности
    const currentHours = { ...(form.working_hours as WorkingHoursData) };
    
    // Инициализируем день, если его еще нет
    if (!currentHours[day as keyof WorkingHoursData]) {
        currentHours[day as keyof WorkingHoursData] = { is_closed: false, start: '08:00', end: '22:00' };
    }
    
    // Обновляем значение, создавая новый объект для дня
    const dayData = { ...currentHours[day as keyof WorkingHoursData]! };
    if (field === 'is_closed') {
        dayData.is_closed = value as boolean;
    } else {
        dayData[field] = value as string;
    }
    
    // Обновляем весь объект working_hours
    currentHours[day as keyof WorkingHoursData] = dayData;
    
    // Присваиваем новый объект форме
    form.working_hours = currentHours as any;
};

const submit = () => {
    // Убеждаемся, что все дни недели присутствуют в данных
    const workingHours: WorkingHoursData = {};
    weekDays.forEach(day => {
        const dayData = form.working_hours[day.key as keyof WorkingHoursData];
        if (dayData) {
            workingHours[day.key as keyof WorkingHoursData] = {
                is_closed: dayData.is_closed ?? false,
                start: dayData.start || '08:00',
                end: dayData.end || '22:00',
            };
        } else {
            workingHours[day.key as keyof WorkingHoursData] = {
                is_closed: false,
                start: '08:00',
                end: '22:00',
            };
        }
    });
    
    form.transform(() => ({
        ...form.data(),
        working_hours: workingHours,
    })).put(route('business.update', props.business.id));
};
</script>

<template>
    <Head :title="`Редактировать: ${form.name}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Редактировать точку продаж</h1>
                <p class="text-muted-foreground">
                    Обновите информацию о точке продаж
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о точке продаж</CardTitle>
                        <CardDescription>
                            Обновите данные о точке продаж
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Название точки продаж *</Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="phone">Телефон</Label>
                                <Input id="phone" v-model="form.phone" type="tel" />
                                <p v-if="form.errors.phone" class="text-sm text-destructive">
                                    {{ form.errors.phone }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="address">Адрес</Label>
                            <Textarea id="address" v-model="form.address" rows="2" />
                            <p v-if="form.errors.address" class="text-sm text-destructive">
                                {{ form.errors.address }}
                            </p>
                        </div>

                        <div class="space-y-2">
                            <Label for="email">Email</Label>
                            <Input id="email" v-model="form.email" type="email" />
                            <p v-if="form.errors.email" class="text-sm text-destructive">
                                {{ form.errors.email }}
                            </p>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active" class="font-medium cursor-pointer">
                                Активна
                            </Label>
                        </div>
                    </CardContent>

                    <CardHeader>
                        <CardTitle>Рабочие часы</CardTitle>
                        <CardDescription>
                            Настройте рабочие часы для каждого дня недели
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-4">
                            <div
                                v-for="day in weekDays"
                                :key="day.key"
                                class="flex items-center gap-4 p-4 border rounded-lg"
                            >
                                <div class="flex items-center space-x-2 min-w-[140px]">
                                    <Checkbox
                                        :id="`active-${day.key}`"
                                        :checked="dayCheckedComputed[day.key]?.value ?? false"
                                        @update:checked="(val: boolean) => toggleDayChecked(day.key, val)"
                                    />
                                    <Label :for="`active-${day.key}`" class="font-medium cursor-pointer">
                                        {{ day.label }}
                                    </Label>
                                </div>

                                <div
                                    v-if="dayCheckedComputed[day.key]?.value ?? false"
                                    class="flex items-center gap-2 flex-1"
                                >
                                    <div class="flex items-center gap-2">
                                        <Label :for="`start-${day.key}`" class="text-sm text-muted-foreground">С</Label>
                                        <Input
                                            :id="`start-${day.key}`"
                                            type="time"
                                            :model-value="dayDataComputed[day.key]?.value.start || '08:00'"
                                            @update:model-value="updateWorkingHours(day.key, 'start', $event)"
                                            class="w-32"
                                        />
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <Label :for="`end-${day.key}`" class="text-sm text-muted-foreground">До</Label>
                                        <Input
                                            :id="`end-${day.key}`"
                                            type="time"
                                            :model-value="dayDataComputed[day.key]?.value.end || '22:00'"
                                            @update:model-value="updateWorkingHours(day.key, 'end', $event)"
                                            class="w-32"
                                        />
                                    </div>
                                </div>

                                <div
                                    v-else
                                    class="flex-1 text-sm text-muted-foreground"
                                >
                                    Выходной
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('business.index'))">
                            Отмена
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Сохранение...' : 'Сохранить изменения' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
