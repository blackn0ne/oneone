<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { PlusIcon, Eye, Pencil, Trash2 } from 'lucide-vue-next';
import ServiceCreateForm from '@/components/Forms/ServiceCreateForm.vue';
import type { Service, Business } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    services: {
        data: Service[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    businesses?: Business[];
}

const props = defineProps<Props>();

const isCreateSheetOpen = ref(false);

const handleDelete = (service: Service) => {
    if (confirm('Вы уверены, что хотите удалить эту услугу?')) {
        router.delete(route('services.destroy', service.id));
    }
};

const openCreateSheet = () => {
    isCreateSheetOpen.value = true;
};

const closeCreateSheet = (open: boolean) => {
    if (!open) {
        isCreateSheetOpen.value = false;
    }
};

const handleSuccess = () => {
    isCreateSheetOpen.value = false;
};
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
                <Button @click="openCreateSheet">
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Добавить услугу
                </Button>
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
                                <TableCell>{{ service.price?.toLocaleString('ru-RU') }} ₸</TableCell>
                                <TableCell>
                                    <Badge variant="outline">{{ service.booking_mode }}</Badge>
                                </TableCell>
                                <TableCell>
                                    <Badge :variant="service.is_active ? 'default' : 'secondary'">
                                        {{ service.is_active ? 'Активна' : 'Неактивна' }}
                                    </Badge>
                                </TableCell>
                                <TableCell class="text-right">
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('services.show', service.id)">
                                            <Button variant="ghost" size="icon" class="rounded-full">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link :href="route('services.edit', service.id)">
                                            <Button variant="ghost" size="icon" class="rounded-full">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="rounded-full"
                                            @click="handleDelete(service)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
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

            <!-- Sheet для создания услуги -->
            <Sheet :open="isCreateSheetOpen" @update:open="closeCreateSheet">
                <SheetContent side="right" class="flex flex-col p-0">
                    <SheetHeader class="border-b p-4">
                        <SheetTitle><h4 class="text-lg font-semibold">Новая услуга</h4></SheetTitle>
                    </SheetHeader>

                    <div class="flex-1 overflow-y-auto">
                        <ServiceCreateForm
                            :businesses="props.businesses || []"
                            :on-success="handleSuccess"
                            :on-cancel="() => closeCreateSheet(false)"
                        />
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    </AppLayout>
</template>
