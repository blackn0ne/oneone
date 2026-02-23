<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { route } from '@/lib/routes';

interface Plan {
    id: number;
    name: string;
    slug: string;
}

interface Props {
    plans: Plan[];
}

const props = defineProps<Props>();

const form = useForm({
    id: '',
    name: '',
    email: '',
    phone: '',
    plan_id: '',
    status: 'trial',
});

const submit = () => {
    form.post(route('central.tenants.store'));
};
</script>

<template>
    <Head title="Создать Tenant" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Создать Tenant</h1>
                <p class="text-muted-foreground">
                    Создайте нового клиента платформы
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о Tenant</CardTitle>
                        <CardDescription>
                            Заполните форму для создания нового tenant
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label for="id">ID *</Label>
                            <Input
                                id="id"
                                v-model="form.id"
                                placeholder="test-tenant"
                                required
                            />
                            <p class="text-xs text-muted-foreground">
                                Уникальный идентификатор (будет использован для поддомена)
                            </p>
                            <p v-if="form.errors.id" class="text-sm text-destructive">
                                {{ form.errors.id }}
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Название *</Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email *</Label>
                                <Input id="email" v-model="form.email" type="email" required />
                                <p v-if="form.errors.email" class="text-sm text-destructive">
                                    {{ form.errors.email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="phone">Телефон</Label>
                                <Input id="phone" v-model="form.phone" type="tel" />
                            </div>

                            <div class="space-y-2">
                                <Label for="plan">Тарифный план</Label>
                                <Select v-model="form.plan_id">
                                    <SelectTrigger id="plan">
                                        <SelectValue placeholder="Выберите тариф" />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="">Без тарифа</SelectItem>
                                        <SelectItem
                                            v-for="plan in plans"
                                            :key="plan.id"
                                            :value="String(plan.id)"
                                        >
                                            {{ plan.name }}
                                        </SelectItem>
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Статус</Label>
                            <Select v-model="form.status">
                                <SelectTrigger id="status">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="trial">Пробный период</SelectItem>
                                    <SelectItem value="active">Активен</SelectItem>
                                    <SelectItem value="suspended">Приостановлен</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('central.tenants.index'))">
                            Отмена
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Создание...' : 'Создать Tenant' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
