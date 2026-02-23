<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { route } from '@/lib/routes';

const form = useForm({
    name: '',
    slug: '',
    description: '',
    price: '',
    currency: 'USD',
    interval: 'monthly',
    features: null,
    is_active: true,
    sort_order: 0,
});

// Автоматически генерируем slug из названия
const generateSlug = (name: string) => {
    return name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-+|-+$/g, '');
};

const handleNameChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const value = target.value;
    form.name = value;
    if (!form.slug || form.slug === generateSlug(form.name)) {
        form.slug = generateSlug(value);
    }
};

const submit = () => {
    form.post(route('central.plans.store'));
};
</script>

<template>
    <Head title="Создать тарифный план" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Создать тарифный план</h1>
                <p class="text-muted-foreground">
                    Создайте новый тарифный план для платформы
                </p>
            </div>

            <Card>
                <form @submit.prevent="submit">
                    <CardHeader>
                        <CardTitle>Информация о плане</CardTitle>
                        <CardDescription>
                            Заполните форму для создания нового тарифного плана
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid gap-4 md:grid-cols-2">
                            <div class="space-y-2">
                                <Label for="name">Название *</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    placeholder="Базовый план"
                                    required
                                    @input="handleNameChange"
                                />
                                <p v-if="form.errors.name" class="text-sm text-destructive">
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="slug">Slug *</Label>
                                <Input
                                    id="slug"
                                    v-model="form.slug"
                                    placeholder="basic-plan"
                                    required
                                />
                                <p class="text-xs text-muted-foreground">
                                    Уникальный идентификатор (только латиница, цифры и дефисы)
                                </p>
                                <p v-if="form.errors.slug" class="text-sm text-destructive">
                                    {{ form.errors.slug }}
                                </p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="description">Описание</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="Описание тарифного плана..."
                                rows="3"
                            />
                            <p v-if="form.errors.description" class="text-sm text-destructive">
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="grid gap-4 md:grid-cols-3">
                            <div class="space-y-2">
                                <Label for="price">Цена *</Label>
                                <Input
                                    id="price"
                                    v-model="form.price"
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    placeholder="0.00"
                                    required
                                />
                                <p v-if="form.errors.price" class="text-sm text-destructive">
                                    {{ form.errors.price }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="currency">Валюта *</Label>
                                <Select v-model="form.currency">
                                    <SelectTrigger id="currency">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="USD">USD</SelectItem>
                                        <SelectItem value="EUR">EUR</SelectItem>
                                        <SelectItem value="RUB">RUB</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.currency" class="text-sm text-destructive">
                                    {{ form.errors.currency }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="interval">Интервал *</Label>
                                <Select v-model="form.interval">
                                    <SelectTrigger id="interval">
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="monthly">Месяц</SelectItem>
                                        <SelectItem value="yearly">Год</SelectItem>
                                    </SelectContent>
                                </Select>
                                <p v-if="form.errors.interval" class="text-sm text-destructive">
                                    {{ form.errors.interval }}
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
                                <p class="text-xs text-muted-foreground">
                                    Чем меньше число, тем выше план в списке
                                </p>
                                <p v-if="form.errors.sort_order" class="text-sm text-destructive">
                                    {{ form.errors.sort_order }}
                                </p>
                            </div>

                            <div class="flex items-center space-x-2 pt-8">
                                <Switch
                                    id="is_active"
                                    v-model:checked="form.is_active"
                                />
                                <Label for="is_active" class="cursor-pointer">
                                    План активен
                                </Label>
                            </div>
                        </div>

                        <div v-if="form.errors.features" class="text-sm text-destructive">
                            {{ form.errors.features }}
                        </div>
                    </CardContent>
                    <CardFooter class="flex justify-end gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            @click="$inertia.visit(route('central.plans.index'))"
                        >
                            Отмена
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ form.processing ? 'Создание...' : 'Создать план' }}
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </div>
    </AppLayout>
</template>
