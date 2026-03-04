<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Checkbox } from '@/components/ui/checkbox';
import { route } from '@/lib/routes';

interface Permission {
    id: number;
    name: string;
}

interface Props {
    permissions: Record<string, Permission[]>;
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    specialization: '',
    is_active: true,
    permissions: [] as number[],
});

const submit = () => {
    form.post(route('staff.store'));
};

const togglePermission = (permissionId: number) => {
    const index = form.permissions.indexOf(permissionId);
    if (index > -1) {
        form.permissions.splice(index, 1);
    } else {
        form.permissions.push(permissionId);
    }
};

const toggleAllInGroup = (groupPermissions: Permission[]) => {
    const allSelected = groupPermissions.every(p => form.permissions.includes(p.id));
    if (allSelected) {
        groupPermissions.forEach(p => {
            const index = form.permissions.indexOf(p.id);
            if (index > -1) form.permissions.splice(index, 1);
        });
    } else {
        groupPermissions.forEach(p => {
            if (!form.permissions.includes(p.id)) {
                form.permissions.push(p.id);
            }
        });
    }
};

const getModuleLabel = (module: string): string => {
    const labels: Record<string, string> = {
        'view': 'Просмотр',
        'create': 'Создание',
        'edit': 'Редактирование',
        'delete': 'Удаление',
        'cancel': 'Отмена',
        'assign': 'Назначение',
        'manage': 'Управление',
        'bookings': 'Бронирования',
        'services': 'Услуги',
        'staff': 'Сотрудники',
        'customers': 'Клиенты',
        'locations': 'Локации',
        'settings': 'Настройки',
        'roles': 'Роли',
        'reports': 'Отчеты',
        'all': 'Все',
    };
    return labels[module] || module;
};

const formatPermissionName = (name: string): string => {
    const parts = name.split(' ');
    if (parts.length > 1) {
        const action = getModuleLabel(parts[0]);
        const module = getModuleLabel(parts[1]);
        return `${action} ${module}`;
    }
    return name;
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
                            <Checkbox id="is_active" v-model:checked="form.is_active" />
                            <Label for="is_active" class="cursor-pointer">Активен</Label>
                        </div>

                        <div class="space-y-4 border-t pt-4">
                            <div>
                                <Label class="text-base font-semibold">Разрешения</Label>
                                <p class="text-sm text-muted-foreground mb-4">
                                    Выберите разрешения для сотрудника. Разрешения сгруппированы по модулям.
                                </p>
                            </div>
                            <div class="space-y-4">
                                <div
                                    v-for="(group, module) in permissions"
                                    :key="module"
                                    class="space-y-2 border rounded-lg p-4"
                                >
                                    <div class="flex items-center justify-between mb-2">
                                        <Label class="text-base font-semibold capitalize">
                                            {{ getModuleLabel(module) }}
                                        </Label>
                                        <Button
                                            type="button"
                                            variant="ghost"
                                            size="sm"
                                            @click="toggleAllInGroup(group)"
                                        >
                                            {{ group.every(p => form.permissions.includes(p.id)) ? 'Снять все' : 'Выбрать все' }}
                                        </Button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                                        <div
                                            v-for="permission in group"
                                            :key="permission.id"
                                            class="flex items-center space-x-2"
                                        >
                                            <Checkbox
                                                :id="`permission-${permission.id}`"
                                                :checked="form.permissions.includes(permission.id)"
                                                @update:checked="togglePermission(permission.id)"
                                            />
                                            <Label
                                                :for="`permission-${permission.id}`"
                                                class="text-sm font-normal cursor-pointer"
                                            >
                                                {{ formatPermissionName(permission.name) }}
                                            </Label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <p v-if="form.errors.permissions" class="text-sm text-destructive">
                                {{ form.errors.permissions }}
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
