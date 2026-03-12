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

interface Staff {
    id: number;
    name: string;
    email?: string;
    phone?: string;
    specialization?: string;
    is_active: boolean;
    roles?: Role[];
}

interface Props {
    staff: Staff;
    roles: Role[];
}

const props = defineProps<Props>();

const form = useForm({
    name: props.staff.name,
    email: props.staff.email || '',
    phone: props.staff.phone || '',
    specialization: props.staff.specialization || '',
    is_active: props.staff.is_active,
    role_id: (props.staff.roles && props.staff.roles.length > 0) ? String(props.staff.roles[0].id) : null as string | null,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('staff.update', props.staff.id));
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
    <Head title="Редактировать сотрудника" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Редактировать сотрудника</h1>
                <p class="text-muted-foreground">
                    Измените информацию о сотруднике
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о сотруднике</CardTitle>
                        <CardDescription>
                            Заполните форму для редактирования сотрудника
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

                        <div class="space-y-2 border-t pt-4">
                            <Label class="text-base font-semibold">Смена пароля</Label>
                            <p class="text-sm text-muted-foreground">
                                Оставьте пустым, если не хотите менять пароль
                            </p>
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="password">Новый пароль</Label>
                                    <Input
                                        id="password"
                                        v-model="form.password"
                                        type="password"
                                        autocomplete="new-password"
                                    />
                                    <p v-if="form.errors.password" class="text-sm text-destructive">
                                        {{ form.errors.password }}
                                    </p>
                                </div>
                                <div class="space-y-2">
                                    <Label for="password_confirmation">Подтверждение пароля</Label>
                                    <Input
                                        id="password_confirmation"
                                        v-model="form.password_confirmation"
                                        type="password"
                                        autocomplete="new-password"
                                    />
                                    <p v-if="form.errors.password_confirmation" class="text-sm text-destructive">
                                        {{ form.errors.password_confirmation }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('staff.index'))">
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
