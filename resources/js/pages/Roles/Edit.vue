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

interface Role {
    id: number;
    name: string;
    permissions?: Permission[];
}

interface Props {
    role: Role;
    permissions: Record<string, Permission[]>;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.role.name,
    permissions: (props.role.permissions || []).map(p => p.id) as number[],
});

const submit = () => {
    form.put(route('roles.update', props.role.id));
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
</script>

<template>
    <Head title="Редактировать роль" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Редактировать роль</h1>
                <p class="text-muted-foreground">
                    Измените информацию о роли и её разрешениях
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о роли</CardTitle>
                        <CardDescription>
                            Заполните форму для редактирования роли
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="space-y-2">
                            <Label for="name">Название роли *</Label>
                            <Input id="name" v-model="form.name" required />
                            <p v-if="form.errors.name" class="text-sm text-destructive">
                                {{ form.errors.name }}
                            </p>
                        </div>

                        <div class="space-y-4">
                            <Label>Разрешения</Label>
                            <div v-for="(group, module) in permissions" :key="module" class="space-y-2 border rounded-lg p-4">
                                <div class="flex items-center justify-between mb-2">
                                    <Label class="text-base font-semibold capitalize">{{ module }}</Label>
                                    <Button
                                        type="button"
                                        variant="ghost"
                                        size="sm"
                                        @click="toggleAllInGroup(group)"
                                    >
                                        {{ group.every(p => form.permissions.includes(p.id)) ? 'Снять все' : 'Выбрать все' }}
                                    </Button>
                                </div>
                                <div class="grid grid-cols-2 md:grid-cols-3 gap-2">
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
                                            {{ permission.name }}
                                        </Label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('roles.index'))">
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
