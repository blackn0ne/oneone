<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import type { Service, Location } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    service: Service;
    locations: Location[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.service.name,
    description: props.service.description || '',
    duration: props.service.duration,
    price: props.service.price,
    location_id: props.service.location_id ? String(props.service.location_id) : '',
    is_active: props.service.is_active,
    booking_mode: props.service.booking_mode,
    buffer_time_before: props.service.buffer_time_before || 0,
    buffer_time_after: props.service.buffer_time_after || 0,
    prepare_time: props.service.prepare_time || 0,
    max_participants: props.service.max_participants,
    allow_custom_duration: props.service.allow_custom_duration || false,
    allow_recurring: props.service.allow_recurring || false,
});

const submit = () => {
    const submitData = { ...form.data() };
    if (!submitData.location_id || submitData.location_id === '') {
        submitData.location_id = null;
    } else {
        submitData.location_id = Number(submitData.location_id);
    }
    form.transform(() => submitData).put(route('services.update', props.service.id));
};
</script>

<template>
    <Head :title="`Редактировать: ${form.name}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Редактировать услугу</h1>
                <p class="text-muted-foreground">
                    Обновите информацию об услуге
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация об услуге</CardTitle>
                        <CardDescription>
                            Обновите данные услуги
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Название *</Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="price">Цена (₽) *</Label>
                                <Input id="price" v-model.number="form.price" type="number" min="0" step="0.01" required />
                                <p v-if="form.errors.price" class="text-sm text-destructive">
                                    {{ form.errors.price }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Описание</Label>
                            <Textarea id="description" v-model="form.description" rows="3" />
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="duration">Длительность (мин) *</Label>
                                <Input id="duration" v-model.number="form.duration" type="number" min="1" required />
                            </div>

                            <div class="space-y-2">
                                <Label for="booking_mode">Режим бронирования *</Label>
                                <Select v-model="form.booking_mode" required>
                                    <SelectTrigger id="booking_mode">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="service">Услуга</SelectItem>
                                        <SelectItem value="hotel">Отель</SelectItem>
                                        <SelectItem value="event">Событие</SelectItem>
                                        <SelectItem value="online">Онлайн</SelectItem>
                                        <SelectItem value="rental">Аренда</SelectItem>
                                        <SelectItem value="chauffeur">Водитель</SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>

                            <div class="space-y-2">
                                <Label for="location">Локация</Label>
                                <Select v-model="form.location_id">
                                    <SelectTrigger id="location">
                                        <SelectValue placeholder="Выберите локацию (опционально)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Нет локации</SelectItem>
                                        <SelectItem
                                            v-for="location in locations"
                                            :key="location.id"
                                            :value="String(location.id)"
                                        >
                                            {{ location.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <Label class="text-base font-semibold">Временные интервалы</Label>
                            <div class="grid gap-4 md:grid-cols-3">
                                <div class="space-y-2">
                                    <Label for="buffer_before">Буфер до начала (мин)</Label>
                                    <Input id="buffer_before" v-model.number="form.buffer_time_before" type="number" min="0" />
                                    <p class="text-xs text-muted-foreground">
                                        Время, зарезервированное перед началом услуги (например, для подготовки оборудования)
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="buffer_after">Буфер после окончания (мин)</Label>
                                    <Input id="buffer_after" v-model.number="form.buffer_time_after" type="number" min="0" />
                                    <p class="text-xs text-muted-foreground">
                                        Время, зарезервированное после окончания услуги (например, для уборки, перерыва)
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="prepare_time">Время подготовки (мин)</Label>
                                    <Input id="prepare_time" v-model.number="form.prepare_time" type="number" min="0" />
                                    <p class="text-xs text-muted-foreground">
                                        Время, необходимое сотруднику для подготовки к услуге
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="max_participants">Макс. участников</Label>
                            <Input id="max_participants" v-model.number="form.max_participants" type="number" min="1" />
                            <p class="text-xs text-muted-foreground">
                                Оставьте пустым для индивидуальных услуг
                            </p>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_active" v-model:checked="form.is_active" />
                                <Label for="is_active" class="cursor-pointer">Активна</Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="allow_custom_duration" v-model:checked="form.allow_custom_duration" />
                                <Label for="allow_custom_duration" class="cursor-pointer">
                                    Разрешить кастомную длительность
                                </Label>
                            </div>

                            <div class="flex items-center space-x-2">
                                <Checkbox id="allow_recurring" v-model:checked="form.allow_recurring" />
                                <Label for="allow_recurring" class="cursor-pointer">
                                    Разрешить повторяющиеся бронирования
                                </Label>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('services.index'))">
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
