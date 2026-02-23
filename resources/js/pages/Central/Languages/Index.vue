<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { PlusIcon } from 'lucide-vue-next';
import { route } from '@/lib/routes';

interface Language {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
    sort_order: number;
}

interface Props {
    languages: Language[];
}

const props = defineProps<Props>();
</script>

<template>
    <Head title="Языки" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Языки</h1>
                    <p class="text-muted-foreground">
                        Управление языками платформы
                    </p>
                </div>
                <Link :href="route('central.languages.create')">
                    <Button>
                        <PlusIcon class="mr-2 h-4 w-4" />
                        Добавить язык
                    </Button>
                </Link>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все языки</CardTitle>
                    <CardDescription>Список всех доступных языков</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Название</TableHead>
                                <TableHead>Код</TableHead>
                                <TableHead>Статус</TableHead>
                                <TableHead>Порядок</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="language in props.languages" :key="language.id">
                                <TableCell class="font-medium">{{ language.name }}</TableCell>
                                <TableCell>{{ language.code }}</TableCell>
                                <TableCell>
                                    <Badge :variant="language.is_active ? 'default' : 'secondary'">
                                        {{ language.is_active ? 'Активен' : 'Неактивен' }}
                                    </Badge>
                                </TableCell>
                                <TableCell>{{ language.sort_order }}</TableCell>
                                <TableCell class="text-right">
                                    <Link :href="route('central.languages.show', language.id)">
                                        <Button variant="ghost" size="sm">Просмотр</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="props.languages.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                    Нет языков
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
