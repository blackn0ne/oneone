<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import WorkingHoursForm from '@/components/Forms/WorkingHoursForm.vue';
import { route } from '@/lib/routes';

const form = useForm({
    name: '',
    address: '',
    phone: '',
    email: '',
    is_active: true,
    working_hours: {
        monday: { is_closed: false, start: '08:00', end: '22:00' },
        tuesday: { is_closed: false, start: '08:00', end: '22:00' },
        wednesday: { is_closed: false, start: '08:00', end: '22:00' },
        thursday: { is_closed: false, start: '08:00', end: '22:00' },
        friday: { is_closed: false, start: '08:00', end: '22:00' },
        saturday: { is_closed: false, start: '08:00', end: '22:00' },
        sunday: { is_closed: false, start: '08:00', end: '22:00' },
    },
});

const submit = () => {
    form.post(route('business.store'), {
        onSuccess: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <Head title="Новая точка продаж" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Новая точка продаж</h1>
                <p class="text-muted-foreground">
                    Создайте новую точку продаж для бронирования
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о точке продаж</CardTitle>
                        <CardDescription>
                            Заполните данные о точке продаж
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
                            <Switch
                                id="is_active"
                                v-model="form.is_active"
                            />
                            <Label for="is_active" class="font-medium cursor-pointer">
                                Активна
                            </Label>
                        </div>

                        <div class="border-t pt-4">
                            <WorkingHoursForm v-model="form.working_hours" />
                        </div>
                    </CardContent>

                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('business.index'))">
                            Отмена
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Создание...' : 'Создать точку продаж' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
