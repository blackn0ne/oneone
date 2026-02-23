<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import type { Staff } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    staff: {
        data: Staff[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Сотрудники" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Сотрудники</h1>
                    <p class="text-muted-foreground">
                        Управляйте сотрудниками и их расписанием
                    </p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все сотрудники</CardTitle>
                    <CardDescription>Список всех сотрудников</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Имя</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Телефон</TableHead>
                                <TableHead>Специализация</TableHead>
                                <TableHead>Бронирований</TableHead>
                                <TableHead>Статус</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="member in staff.data" :key="member.id">
                                <TableCell class="font-medium">{{ member.name }}</TableCell>
                                <TableCell>{{ member.email || '-' }}</TableCell>
                                <TableCell>{{ member.phone || '-' }}</TableCell>
                                <TableCell>{{ member.specialization || '-' }}</TableCell>
                                <TableCell>{{ member.bookings_count || 0 }}</TableCell>
                                <TableCell>
                                    <Badge :variant="member.is_active ? 'default' : 'secondary'">
                                        {{ member.is_active ? 'Активен' : 'Неактивен' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <Link :href="route('staff.show', member.id)">
                                        <Button variant="ghost" size="sm">Просмотр</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="staff.data.length === 0">
                                <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                    Нет сотрудников
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
