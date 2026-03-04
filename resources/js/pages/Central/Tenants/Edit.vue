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

interface Tenant {
    id: string;
    name: string;
    email: string;
    phone?: string;
    status: 'active' | 'suspended' | 'trial';
    plan?: {
        id: number;
        name: string;
    };
    subscription?: {
        id: number;
        status: string;
    };
    domains?: Array<{
        id: number;
        domain: string;
    }>;
}

interface Props {
    tenant: Tenant;
    plans: Plan[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.tenant.name,
    email: props.tenant.email,
    phone: props.tenant.phone || '',
    plan_id: props.tenant.plan?.id ? String(props.tenant.plan.id) : '',
    status: props.tenant.status,
});

const submit = () => {
    form.put(route('central.tenants.update', props.tenant.id));
};
</script>

<template>
    <Head title="Редактировать Tenant" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Редактировать Tenant</h1>
                <p class="text-muted-foreground">
                    Измените информацию о клиенте платформы
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о Tenant</CardTitle>
                        <CardDescription>
                            ID: {{ tenant.id }} (нельзя изменить)
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
                        <Button type="button" variant="outline" @click="$inertia.visit(route('central.tenants.show', tenant.id))">
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
