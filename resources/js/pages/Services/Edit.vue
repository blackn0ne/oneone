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
import type { Service, Business } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    service: Service;
    businesses: Business[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.service.name,
    description: props.service.description || '',
    duration: props.service.duration,
    price: props.service.price,
    business_id: props.service.business_id ? String(props.service.business_id) : '',
    is_active: props.service.is_active,
    booking_mode: props.service.booking_mode,
});

const submit = () => {
    const submitData = { ...form.data() };
    if (!submitData.business_id || submitData.business_id === '') {
        submitData.business_id = null;
    } else {
        submitData.business_id = Number(submitData.business_id);
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
                                <Label for="price">Цена (₸) *</Label>
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
                                <Label for="business">Точка продаж</Label>
                                <Select v-model="form.business_id">
                                    <SelectTrigger id="business">
                                        <SelectValue placeholder="Выберите точку продаж (опционально)" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Нет точки продаж</SelectItem>
                                        <SelectItem
                                            v-for="business in businesses"
                                            :key="business.id"
                                            :value="String(business.id)"
                                        >
                                            {{ business.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div class="flex items-center space-x-2">
                                <Checkbox id="is_active" v-model="form.is_active" />
                                <Label for="is_active" class="cursor-pointer">Активна</Label>
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
