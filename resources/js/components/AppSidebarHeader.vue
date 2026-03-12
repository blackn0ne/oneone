<script setup lang="ts">
import { usePage, router } from '@inertiajs/vue3';
import { computed } from 'vue';
import { ArrowLeft } from 'lucide-vue-next';
import Breadcrumbs from '@/components/Breadcrumbs.vue';
import { SidebarTrigger } from '@/components/ui/sidebar';
import { Button } from '@/components/ui/button';
import {
    Tooltip,
    TooltipContent,
    TooltipProvider,
    TooltipTrigger,
} from '@/components/ui/tooltip';
import { route } from '@/lib/routes';
import type { BreadcrumbItem } from '@/types';

withDefaults(
    defineProps<{
        breadcrumbs?: BreadcrumbItem[];
    }>(),
    {
        breadcrumbs: () => [],
    },
);

const page = usePage();
const auth = computed(() => page.props.auth ?? { user: null });
const user = computed(() => auth.value.user ?? null);

// Проверяем, что мы на странице тенанта (не /central) и пользователь суперадмин
const isTenantPage = computed(() => {
    const url = page.url;
    return !url.startsWith('/central') && url !== '/dashboard';
});

const isSuperAdmin = computed(() => {
    return (user.value?.role === 'super_admin') || (user.value?.is_super_admin ?? false);
});

const showBackButton = computed(() => {
    return isTenantPage.value && isSuperAdmin.value;
});

const goBackToCentral = () => {
    router.visit(route('central.dashboard'));
};
</script>

<template>
    <header
        class="flex h-16 shrink-0 items-center justify-between gap-2 border-b border-sidebar-border/70 px-6 transition-[width,height] ease-linear group-has-data-[collapsible=icon]/sidebar-wrapper:h-12 md:px-4"
    >
        <div class="flex items-center gap-2">
            <SidebarTrigger class="-ml-1" />
            <template v-if="breadcrumbs && breadcrumbs.length > 0">
                <Breadcrumbs :breadcrumbs="breadcrumbs" />
            </template>
        </div>
        <div v-if="showBackButton" class="flex items-center">
            <TooltipProvider>
                <Tooltip>
                    <TooltipTrigger as-child>
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-9 w-9"
                            @click="goBackToCentral"
                        >
                            <ArrowLeft class="h-4 w-4" />
                        </Button>
                    </TooltipTrigger>
                    <TooltipContent>
                        <p>Вернуться к панели суперадмина</p>
                    </TooltipContent>
                </Tooltip>
            </TooltipProvider>
        </div>
    </header>
</template>
