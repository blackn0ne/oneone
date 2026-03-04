<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { Card, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Building2, ExternalLink, LogOut } from 'lucide-vue-next';
import { logout } from '@/routes';
import { route } from '@/lib/routes';

interface Tenant {
    id: string;
    name: string;
    email: string;
}

interface Props {
    tenants: Tenant[];
}

defineProps<Props>();

const handleLogout = () => {
    router.post(logout.url());
};
</script>

<template>
    <Head title="Мои компании" />

    <div class="flex min-h-screen flex-col bg-slate-50 dark:bg-slate-950">
        <!-- Шапка с кнопкой выхода -->
        <header class="flex items-center justify-end border-b bg-background px-4 py-3">
            <Button variant="ghost" size="sm" @click="handleLogout">
                <LogOut class="mr-2 h-4 w-4" />
                Выйти
            </Button>
        </header>

        <div class="flex flex-1 flex-col items-center justify-center p-4">
        <div class="w-full max-w-md space-y-6">
            <div class="text-center">
                <h1 class="text-2xl font-bold tracking-tight">Мои компании</h1>
                <p class="mt-2 text-muted-foreground">
                    Выберите компанию для перехода в панель управления
                </p>
            </div>

            <div class="space-y-4">
                <Link
                    v-for="tenant in tenants"
                    :key="tenant.id"
                    :href="route('setTenant', tenant.id)"
                >
                    <Card class="hover:border-primary/50 transition-colors cursor-pointer">
                        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                            <div class="flex items-center gap-3">
                                <div class="flex h-10 w-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                    <Building2 class="h-5 w-5" />
                                </div>
                                <div>
                                    <CardTitle class="text-base">{{ tenant.name }}</CardTitle>
                                    <CardDescription v-if="tenant.email">{{ tenant.email }}</CardDescription>
                                </div>
                            </div>
                            <ExternalLink class="h-4 w-4 text-muted-foreground" />
                        </CardHeader>
                    </Card>
                </Link>
            </div>

            <p v-if="tenants.length === 0" class="text-center text-sm text-muted-foreground">
                У вас нет доступа ни к одной компании. Обратитесь к администратору.
            </p>
        </div>
        </div>
    </div>
</template>
