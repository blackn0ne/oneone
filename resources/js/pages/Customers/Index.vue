<script setup lang="ts">
import { ref } from 'vue';
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Button } from '@/components/ui/button';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import { PlusIcon } from 'lucide-vue-next';
import CustomerCreateForm from '@/components/Forms/CustomerCreateForm.vue';
import type { Customer } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    customers: {
        data: Customer[];
        links: any[];
        current_page: number;
        last_page: number;
    };
}

const props = defineProps<Props>();

const isCreateSheetOpen = ref(false);

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
    <Head title="Клиенты" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Клиенты</h1>
                    <p class="text-muted-foreground">
                        Управляйте клиентами и их информацией
                    </p>
                </div>
                <Button @click="openCreateSheet">
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Добавить клиента
                </Button>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Все клиенты</CardTitle>
                    <CardDescription>Список всех клиентов</CardDescription>
                </CardHeader>
                <CardContent>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Имя</TableHead>
                                <TableHead>Email</TableHead>
                                <TableHead>Телефон</TableHead>
                                <TableHead>Бронирований</TableHead>
                                <TableHead class="text-right">Действия</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            <TableRow v-for="customer in customers.data" :key="customer.id">
                                <TableCell class="font-medium">{{ customer.name }}</TableCell>
                                <TableCell>{{ customer.email || '-' }}</TableCell>
                                <TableCell>{{ customer.phone || '-' }}</TableCell>
                                <TableCell>{{ customer.bookings_count || 0 }}</TableCell>
                                <TableCell class="text-right">
                                    <Link :href="route('customers.show', customer.id)">
                                        <Button variant="ghost" size="sm">Просмотр</Button>
                                    </Link>
                                </TableCell>
                            </TableRow>
                            <TableRow v-if="customers.data.length === 0">
                                <TableCell colspan="5" class="text-center py-8 text-muted-foreground">
                                    Нет клиентов
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </CardContent>
            </Card>

            <!-- Sheet для создания клиента -->
            <Sheet :open="isCreateSheetOpen" @update:open="closeCreateSheet">
                <SheetContent side="right" class="overflow-y-auto">
                    <SheetHeader>
                        <SheetTitle>Новый клиент</SheetTitle>
                        <SheetDescription>
                            Создайте нового клиента в системе
                        </SheetDescription>
                    </SheetHeader>

                    <div class="mt-6">
                        <CustomerCreateForm
                            :on-success="handleSuccess"
                            :on-cancel="() => closeCreateSheet(false)"
                        />
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    </AppLayout>
</template>
