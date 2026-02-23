<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Service } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    service: Service;
}

const props = defineProps<Props>();
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
                <Link :href="route('services.index')">
                    <Button variant="outline">Назад к списку</Button>
                </Link>
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
                                <p class="text-sm font-medium text-muted-foreground">Буфер до</p>
                                <p>{{ service.buffer_time_before }} мин</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Буфер после</p>
                                <p>{{ service.buffer_time_after }} мин</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Время подготовки</p>
                                <p>{{ service.prepare_time }} мин</p>
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
