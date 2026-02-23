<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Staff } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    staff: Staff;
}

const props = defineProps<Props>();
</script>

<template>
    <Head :title="staff.name" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ staff.name }}</h1>
                    <p class="text-muted-foreground">
                        Информация о сотруднике
                    </p>
                </div>
                <Link :href="route('staff.index')">
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
                            <p class="text-sm font-medium text-muted-foreground">Имя</p>
                            <p class="text-lg">{{ staff.name }}</p>
                        </div>

                        <div v-if="staff.email">
                            <p class="text-sm font-medium text-muted-foreground">Email</p>
                            <p>{{ staff.email }}</p>
                        </div>

                        <div v-if="staff.phone">
                            <p class="text-sm font-medium text-muted-foreground">Телефон</p>
                            <p>{{ staff.phone }}</p>
                        </div>

                        <div v-if="staff.specialization">
                            <p class="text-sm font-medium text-muted-foreground">Специализация</p>
                            <p>{{ staff.specialization }}</p>
                        </div>

                        <div>
                            <p class="text-sm font-medium text-muted-foreground">Статус</p>
                            <Badge :variant="staff.is_active ? 'default' : 'secondary'">
                                {{ staff.is_active ? 'Активен' : 'Неактивен' }}
                            </Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Услуги</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div v-if="staff.services && staff.services.length > 0" class="space-y-2">
                            <div
                                v-for="service in staff.services"
                                :key="service.id"
                                class="flex items-center justify-between border-b pb-2"
                            >
                                <span>{{ service.name }}</span>
                                <Badge variant="outline">{{ service.price }} ₽</Badge>
                            </div>
                        </div>
                        <p v-else class="text-muted-foreground">Нет назначенных услуг</p>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
