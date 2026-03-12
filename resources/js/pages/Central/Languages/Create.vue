<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Switch } from '@/components/ui/switch';
import { route } from '@/lib/routes';

const form = useForm({
    name: '',
    code: '',
    is_active: true,
    sort_order: 0,
});

const submit = () => {
    form.post(route('central.languages.store'));
};
</script>

<template>
    <Head title="Создать язык" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Создать язык</h1>
                <p class="text-muted-foreground">
                    Добавьте новый язык для платформы
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о языке</CardTitle>
                        <CardDescription>
                            Заполните форму для создания нового языка
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Название *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Русский"
                                    required
                                />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="code">Код языка *</Label>
                                <Input
                                    id="code"
                                    v-model="form.code"
                                    placeholder="ru"
                                    required
                                />
                                <p class="text-xs text-muted-foreground">
                                    Код языка (например: ru, en, kz)
                                </p>
                                <p v-if="form.errors.code" class="text-sm text-destructive">
                                    {{ form.errors.code }}
                                </p>
                            </div>
                        </div>

                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="sort_order">Порядок сортировки</Label>
                                <Input
                                    id="sort_order"
                                    v-model.number="form.sort_order"
                                    type="number"
                                    min="0"
                                    placeholder="0"
                                />
                                <p v-if="form.errors.sort_order" class="text-sm text-destructive">
                                    {{ form.errors.sort_order }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2 pt-8">
                                <Switch
                                    id="is_active"
                                    v-model="form.is_active"
                                />
                                <Label for="is_active" class="cursor-pointer">
                                    Язык активен
                                </Label>
                            </div>
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit(route('central.languages.index'))"
                        >
                            Отмена
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Создание...' : 'Создать язык' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
