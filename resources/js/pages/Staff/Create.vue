<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { route } from '@/lib/routes';

interface Role {
    id: number;
    name: string;
}

interface Props {
    roles: Role[];
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    specialization: '',
    is_active: true,
    role_id: null as number | null,
});

const submit = () => {
    form.post(route('staff.store'));
};

const getRoleDescription = (roleName: string): string => {
    const descriptions: Record<string, string> = {
        'Мастер': 'Может видеть только свои бронирования и менять их статус',
        'Менеджер': 'Почти все разрешения, кроме отчетов и настроек бизнеса',
        'Админ': 'Все разрешения',
    };
    return descriptions[roleName] || '';
};
</script>

<template>
    <Head title="Новый сотрудник" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Новый сотрудник</h1>
                <p class="text-muted-foreground">
                    Создайте нового сотрудника в системе
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о сотруднике</CardTitle>
                        <CardDescription>
                            Заполните форму для создания нового сотрудника
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Имя *</Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
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

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="phone">Телефон</Label>
                                <Input id="phone" v-model="form.phone" type="tel" />
                                <p v-if="form.errors.phone" class="text-sm text-destructive">
                                    {{ form.errors.phone }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="specialization">Специализация</Label>
                                <Input id="specialization" v-model="form.specialization" />
                                <p v-if="form.errors.specialization" class="text-sm text-destructive">
                                    {{ form.errors.specialization }}
                                </p>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Checkbox id="is_active" v-model="form.is_active" />
                            <Label for="is_active" class="cursor-pointer">Активен</Label>
                        </div>

                        <div class="space-y-2 border-t pt-4">
                            <Label for="role_id">Роль *</Label>
                            <Select
                                id="role_id"
                                v-model="form.role_id"
                                required
                            >
                                <SelectTrigger class="w-full">
                                    <SelectValue placeholder="Выберите роль" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem
                                        v-for="role in roles"
                                        :key="role.id"
                                        :value="String(role.id)"
                                    >
                                        <div class="flex flex-col">
                                            <span class="font-medium">{{ role.name }}</span>
                                            <span class="text-xs text-muted-foreground">{{ getRoleDescription(role.name) }}</span>
                                        </div>
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.role_id" class="text-sm text-destructive">
                                {{ form.errors.role_id }}
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('staff.index'))">
                            Отмена
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Создание...' : 'Создать сотрудника' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
