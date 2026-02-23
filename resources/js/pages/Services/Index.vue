<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PlusIcon } from 'lucide-vue-next';
import type { Service } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    services: {
        data: Service[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Услуги" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Услуги</h1>
                    <p class="text-muted-foreground">
                        Управляйте услугами и их доступностью
                    </p>
                </div>
                <Link :href="route('services.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Добавить услугу
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все услуги</CardTitle>
                    <CardDescription>Список всех услуг</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Название</TableHead>
                                <TableHead>Описание</TableHead>
                                <TableHead>Длительность</TableHead>
                                <TableHead>Цена</TableHead>
                                <TableHead>Режим</TableHead>
                                <TableHead>Статус</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="service in services.data" :key="service.id">
                                <TableCell class="font-medium">{{ service.name }}</TableCell>
                                <TableCell class="max-w-md truncate">
                                    {{ service.description || '-' }}
                                </TableCell>
                                <TableCell>{{ service.duration }} мин</TableCell>
                                <TableCell>{{ service.price?.toLocaleString('ru-RU') }} ₽</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ service.booking_mode }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="service.is_active ? 'default' : 'secondary'">
                                        {{ service.is_active ? 'Активна' : 'Неактивна' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Link :href="route('services.show', service.id)">
                                        <Button variant="ghost" size="sm">Просмотр</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="services.data.length === 0">
                                <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                    Нет услуг
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
