<script setup lang="ts">
import { computed, watch } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
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

interface Business {
    id?: number;
    company_name?: string;
    company_slogan?: string;
    phone?: string;
    email?: string;
    country?: string;
    city?: string;
    address?: string;
    logo?: string;
    favicon?: string;
    global_currency?: string;
    default_language?: string;
    working_hours?: WorkingHoursData;
}

interface Props {
    business: Business | null;
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

const defaultWorkingHours: WorkingHoursData = {
    monday: { is_closed: false, start: '09:00', end: '22:00' },
    tuesday: { is_closed: false, start: '09:00', end: '22:00' },
    wednesday: { is_closed: false, start: '09:00', end: '22:00' },
    thursday: { is_closed: false, start: '09:00', end: '22:00' },
    friday: { is_closed: false, start: '09:00', end: '22:00' },
    saturday: { is_closed: false, start: '09:00', end: '22:00' },
    sunday: { is_closed: false, start: '09:00', end: '22:00' },
};

const getWorkingHours = (): WorkingHoursData => {
    let hours: any = props.business?.working_hours || {};
    
    if (hours && typeof hours === 'object' && !Array.isArray(hours)) {
        try {
            hours = JSON.parse(JSON.stringify(hours));
        } catch (e) {
            hours = {};
        }
    }
    
    if (typeof hours === 'string') {
        try {
            hours = JSON.parse(hours);
        } catch (e) {
            hours = {};
        }
    }
    
    if (Array.isArray(hours)) {
        const obj: any = {};
        hours.forEach((item: any, index: number) => {
            const dayKey = weekDays[index]?.key;
            if (dayKey) {
                obj[dayKey] = item;
            }
        });
        hours = obj;
    }
    
    const result: WorkingHoursData = {};
    
    weekDays.forEach(day => {
        const dayData = hours?.[day.key as keyof WorkingHoursData];
        result[day.key as keyof WorkingHoursData] = {
            is_closed: dayData?.is_closed ?? defaultWorkingHours[day.key as keyof WorkingHoursData]?.is_closed ?? false,
            start: dayData?.start || defaultWorkingHours[day.key as keyof WorkingHoursData]?.start || '09:00',
            end: dayData?.end || defaultWorkingHours[day.key as keyof WorkingHoursData]?.end || '22:00',
        };
    });
    
    return result;
};

const workingHoursComputed = computed(() => getWorkingHours());

const initialWorkingHours = getWorkingHours();
const form = useForm({
    company_name: props.business?.company_name || '',
    company_slogan: props.business?.company_slogan || '',
    phone: props.business?.phone || '',
    email: props.business?.email || '',
    country: props.business?.country || '',
    city: props.business?.city || '',
    address: props.business?.address || '',
    logo: props.business?.logo || '',
    favicon: props.business?.favicon || '',
    global_currency: props.business?.global_currency || 'USD',
    default_language: props.business?.default_language || 'ru',
    working_hours: JSON.parse(JSON.stringify(initialWorkingHours)),
});

const formWorkingHours = computed(() => {
    const hours = form.working_hours as any;
    if (!hours) {
        return defaultWorkingHours;
    }
    
    const result: WorkingHoursData = {};
    weekDays.forEach(day => {
        const dayData = hours[day.key];
        if (dayData && typeof dayData === 'object') {
            result[day.key as keyof WorkingHoursData] = {
                is_closed: dayData.is_closed ?? false,
                start: dayData.start || defaultWorkingHours[day.key as keyof WorkingHoursData]?.start || '09:00',
                end: dayData.end || defaultWorkingHours[day.key as keyof WorkingHoursData]?.end || '22:00',
            };
        } else {
            result[day.key as keyof WorkingHoursData] = {
                is_closed: false,
                start: defaultWorkingHours[day.key as keyof WorkingHoursData]?.start || '09:00',
                end: defaultWorkingHours[day.key as keyof WorkingHoursData]?.end || '22:00',
            };
        }
    });
    return result;
});

watch(() => props.business, (newBusiness) => {
    if (newBusiness) {
        form.company_name = newBusiness.company_name || '';
        form.company_slogan = newBusiness.company_slogan || '';
        form.phone = newBusiness.phone || '';
        form.email = newBusiness.email || '';
        form.country = newBusiness.country || '';
        form.city = newBusiness.city || '';
        form.address = newBusiness.address || '';
        form.logo = newBusiness.logo || '';
        form.favicon = newBusiness.favicon || '';
        form.global_currency = newBusiness.global_currency || 'USD';
        form.default_language = newBusiness.default_language || 'ru';
        
        const hours = workingHoursComputed.value;
        form.working_hours = JSON.parse(JSON.stringify(hours));
    }
}, { deep: true, immediate: true });

const updateWorkingHours = (day: string, field: 'is_closed' | 'start' | 'end', value: boolean | string) => {
    if (!form.working_hours[day as keyof WorkingHoursData]) {
        form.working_hours[day as keyof WorkingHoursData] = { is_closed: false, start: '09:00', end: '22:00' };
    }
    
    if (field === 'is_closed') {
        (form.working_hours as any)[day].is_closed = value as boolean;
    } else {
        (form.working_hours as any)[day][field] = value as string;
    }
};


const submit = () => {
    form.put(route('business.update'));
};
</script>

<template>
    <Head title="Настройки бизнеса" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Настройки бизнеса</h1>
                <p class="text-muted-foreground">
                    Управляйте информацией о вашей компании
                </p>
            </div>

            <form @submit.prevent="submit">
                <!-- Основная информация -->
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
                        <CardDescription>
                            Заполните информацию о вашей компании
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="company_name">Название компании *</Label>
                                <Input id="company_name" v-model="form.company_name" required />
                                <p v-if="form.errors.company_name" class="text-sm text-destructive">
                                    {{ form.errors.company_name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="company_slogan">Слоган</Label>
                                <Input id="company_slogan" v-model="form.company_slogan" />
                                <p v-if="form.errors.company_slogan" class="text-sm text-destructive">
                                    {{ form.errors.company_slogan }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="phone">Телефон</Label>
                                <Input id="phone" v-model="form.phone" type="tel" />
                                <p v-if="form.errors.phone" class="text-sm text-destructive">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email</Label>
                                <Input id="email" v-model="form.email" type="email" />
                                <p v-if="form.errors.email" class="text-sm text-destructive">
                                    {{ form.errors.email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="country">Страна</Label>
                                <Input id="country" v-model="form.country" />
                                <p v-if="form.errors.country" class="text-sm text-destructive">
                                    {{ form.errors.country }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="city">Город</Label>
                                <Input id="city" v-model="form.city" />
                                <p v-if="form.errors.city" class="text-sm text-destructive">
                                    {{ form.errors.city }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="global_currency">Валюта</Label>
                                <Input id="global_currency" v-model="form.global_currency" maxlength="3" placeholder="USD" />
                                <p v-if="form.errors.global_currency" class="text-sm text-destructive">
                                    {{ form.errors.global_currency }}
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

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="logo">Логотип (URL)</Label>
                                <Input id="logo" v-model="form.logo" type="url" />
                                <p v-if="form.errors.logo" class="text-sm text-destructive">
                                    {{ form.errors.logo }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="favicon">Favicon (URL)</Label>
                                <Input id="favicon" v-model="form.favicon" type="url" />
                                <p v-if="form.errors.favicon" class="text-sm text-destructive">
                                    {{ form.errors.favicon }}
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Рабочее время -->
                <Card>
                    <CardHeader>
                        <CardTitle>Рабочее время</CardTitle>
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
                                        :id="`closed-${day.key}`"
                                        :checked="(form.working_hours as any)?.[day.key]?.is_closed ?? false"
                                        @update:checked="updateWorkingHours(day.key, 'is_closed', $event)"
                                    />
                                    <Label :for="`closed-${day.key}`" class="font-medium cursor-pointer">
                                        {{ day.label }}
                                    </Label>
                                </div>

                                <div
                                    v-if="!(formWorkingHours[day.key as keyof WorkingHoursData]?.is_closed ?? false)"
                                    class="flex items-center gap-2 flex-1"
                                >
                                    <div class="flex items-center gap-2">
                                        <Label :for="`start-${day.key}`" class="text-sm text-muted-foreground">С</Label>
                                        <Input
                                            :id="`start-${day.key}`"
                                            type="time"
                                            :model-value="formWorkingHours[day.key as keyof WorkingHoursData]?.start || '09:00'"
                                            @update:model-value="updateWorkingHours(day.key, 'start', $event)"
                                            class="w-32"
                                        />
                                    </div>

                                    <div class="flex items-center gap-2">
                                        <Label :for="`end-${day.key}`" class="text-sm text-muted-foreground">До</Label>
                                        <Input
                                            :id="`end-${day.key}`"
                                            type="time"
                                            :model-value="formWorkingHours[day.key as keyof WorkingHoursData]?.end || '22:00'"
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

                        <p v-if="form.errors.working_hours" class="text-sm text-destructive">
                            {{ form.errors.working_hours }}
                        </p>
                    </CardContent>
                </Card>

                <!-- Кнопка сохранения -->
                <div class="flex justify-end gap-2">
                    <Button type="submit" :disabled="form.processing" size="lg">
                        {{ form.processing ? 'Сохранение...' : 'Сохранить все изменения' }}
                    </Button>
                </div>
            </form>
        </div>
    </AppLayout>
</template>
