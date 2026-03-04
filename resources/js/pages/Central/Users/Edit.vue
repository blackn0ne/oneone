<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { route } from '@/lib/routes';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

interface User {
    id: number;
    name: string;
    email: string;
    is_super_admin: boolean;
    roles?: Array<{ name: string }>;
}

interface Props {
    user: User;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.user.name,
    email: props.user.email,
    password: '',
    password_confirmation: '',
    is_super_admin: props.user.is_super_admin,
    roles: props.user.roles?.map(r => r.name) || [] as string[],
});

const submit = () => {
    form.put(route('central.users.update', props.user.id));
};
</script>

<template>
    <Head :title="t('users.edit_title', 'Редактировать пользователя')" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">{{ t('users.edit_title', 'Редактировать пользователя') }}</h1>
                <p class="text-muted-foreground">
                    {{ t('users.edit_description', 'Измените информацию о пользователе') }}
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>{{ t('users.user_info', 'Информация о пользователе') }}</CardTitle>
                        <CardDescription>
                            {{ t('users.update_form', 'Обновите информацию о пользователе') }}
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">{{ t('users.name', 'Имя') }} *</Label>
                                <Input id="name" v-model="form.name" required />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">{{ t('users.email', 'Email') }} *</Label>
                                <Input id="email" v-model="form.email" type="email" required />
                                <p v-if="form.errors.email" class="text-sm text-destructive">
                                    {{ form.errors.email }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="password">{{ t('users.password', 'Пароль') }}</Label>
                                <Input id="password" v-model="form.password" type="password" />
                                <p class="text-xs text-muted-foreground">
                                    {{ t('users.password_leave_empty', 'Оставьте пустым, чтобы не менять пароль') }}
                                </p>
                                <p v-if="form.errors.password" class="text-sm text-destructive">
                                    {{ form.errors.password }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="password_confirmation">{{ t('users.password_confirmation', 'Подтверждение пароля') }}</Label>
                                <Input id="password_confirmation" v-model="form.password_confirmation" type="password" />
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <Switch
                                id="is_super_admin"
                                v-model:checked="form.is_super_admin"
                            />
                            <Label for="is_super_admin" class="cursor-pointer">
                                {{ t('users.is_super_admin', 'Супер-администратор') }}
                            </Label>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button type="button" variant="outline" @click="$inertia.visit(route('central.users.show', props.user.id))">
                            {{ t('users.cancel', 'Отмена') }}
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? t('users.saving', 'Сохранение...') : t('users.save', 'Сохранить') }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
