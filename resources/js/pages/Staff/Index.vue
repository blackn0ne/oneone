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
import StaffCreateForm from '@/components/Forms/StaffCreateForm.vue';
import type { Staff } from '@/types';
import { route } from '@/lib/routes';

interface Role {
    id: number;
    name: string;
}

interface Props {
    staff: {
        data: Staff[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    roles?: Role[];
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

const handleDelete = (staffMember: Staff) => {
    if (confirm('Вы уверены, что хотите удалить этого сотрудника?')) {
        router.delete(route('staff.destroy', staffMember.id));
    }
};
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
                <Button @click="openCreateSheet">
                    <PlusIcon class="mr-2 h-4 w-4" />
                    Добавить сотрудника
                </Button>
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
                                    <div class="flex justify-end gap-2">
                                        <Link :href="route('staff.show', member.id)">
                                            <Button variant="ghost" size="icon" class="rounded-full">
                                                <Eye class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Link :href="route('staff.edit', member.id)">
                                            <Button variant="ghost" size="icon" class="rounded-full">
                                                <Pencil class="h-4 w-4" />
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="ghost"
                                            size="icon"
                                            class="rounded-full"
                                            @click="handleDelete(member)"
                                        >
                                            <Trash2 class="h-4 w-4" />
                                        </Button>
                                    </div>
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

            <!-- Sheet для создания сотрудника -->
            <Sheet :open="isCreateSheetOpen" @update:open="closeCreateSheet">
                <SheetContent side="right" class="overflow-y-auto">
                    <SheetHeader>
                        <SheetTitle>Новый сотрудник</SheetTitle>
                        <SheetDescription>
                            Создайте нового сотрудника в системе
                        </SheetDescription>
                    </SheetHeader>

                    <div class="mt-6">
                        <StaffCreateForm
                            :roles="props.roles || []"
                            :on-success="handleSuccess"
                            :on-cancel="() => closeCreateSheet(false)"
                        />
                    </div>
                </SheetContent>
            </Sheet>
        </div>
    </AppLayout>
</template>
