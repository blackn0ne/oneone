<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Pencil, Trash2 } from 'lucide-vue-next';
import type { Service } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    service: Service;
}

const props = defineProps<Props>();

const handleDelete = () => {
    if (confirm('Вы уверены, что хотите удалить эту услугу?')) {
        router.delete(route('services.destroy', props.service.id));
    }
};
</script>

<template>
    <Head :title="service.name" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ service.name }}</h1>
                    <p class="text-muted-foreground">
                        Детальная информация об услуге
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Link :href="route('services.edit', service.id)">
                        <Button variant="outline" size="icon" class="rounded-full">
                            <Pencil class="h-4 w-4" />
                        </Button>
                    </Link>
                    <Button variant="outline" size="icon" class="rounded-full" @click="handleDelete">
                        <Trash2 class="h-4 w-4" />
                    </Button>
                    <Link :href="route('services.index')">
                        <Button variant="outline">Назад к списку</Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>Основная информация</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Название</p>
                            <p class="text-lg">{{ service.name }}</p>
                        </div>

                        <div v-if="service.description">
                            <p class="text-sm font-medium text-muted-foreground">Описание</p>
                            <p>{{ service.description }}</p>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Длительность</p>
                                <p>{{ service.duration }} минут</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Цена</p>
                                <p class="text-lg font-semibold">
                                    {{ service.price?.toLocaleString('ru-RU') }} ₽
                                </p>
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Режим бронирования</p>
                            <Badge variant="outline">{{ service.booking_mode }}</Badge>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Статус</p>
                            <Badge :variant="service.is_active ? 'default' : 'secondary'">
                                {{ service.is_active ? 'Активна' : 'Неактивна' }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Настройки</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Буфер до начала</p>
                                <p class="text-base">{{ service.buffer_time_before }} мин</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Время перед началом услуги
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Буфер после окончания</p>
                                <p class="text-base">{{ service.buffer_time_after }} мин</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Время после окончания услуги
                                </p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Время подготовки</p>
                                <p class="text-base">{{ service.prepare_time }} мин</p>
                                <p class="text-xs text-muted-foreground mt-1">
                                    Время подготовки сотрудника
                                </p>
                            </div>
                            <div v-if="service.max_participants">
                                <p class="text-sm font-medium text-muted-foreground">Макс. участников</p>
                                <p>{{ service.max_participants }}</p>
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center gap-2">
                                <Badge :variant="service.allow_custom_duration ? 'default' : 'secondary'">
                                    {{ service.allow_custom_duration ? 'Кастомная длительность' : 'Фиксированная длительность' }}
                                </Badge>
                            </div>
                            <div class="flex items-center gap-2">
                                <Badge :variant="service.allow_recurring ? 'default' : 'secondary'">
                                    {{ service.allow_recurring ? 'Повторяющиеся бронирования' : 'Одноразовые бронирования' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
