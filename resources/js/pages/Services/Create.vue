<script setup lang="ts">
import { ref } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import {
    Sheet,
    SheetContent,
    SheetDescription,
    SheetHeader,
    SheetTitle,
} from '@/components/ui/sheet';
import ServiceCreateForm from '@/components/Forms/ServiceCreateForm.vue';
import type { Location } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    locations: Location[];
}

const props = defineProps<Props>();

const isOpen = ref(true);

const handleClose = (open: boolean) => {
    if (!open) {
        isOpen.value = false;
        window.history.back();
    }
};

const handleSuccess = () => {
    isOpen.value = false;
    window.location.href = route('services.index');
};
</script>

<template>
    <Head title="Новая услуга" />

    <AppLayout>
        <Sheet :open="isOpen" @update:open="handleClose">
            <SheetContent side="right" class="overflow-y-auto">
                <SheetHeader>
                    <SheetTitle>Новая услуга</SheetTitle>
                    <SheetDescription>
                        Создайте новую услугу для бронирования
                    </SheetDescription>
                </SheetHeader>

                <div class="mt-6">
                    <ServiceCreateForm
                        :locations="locations"
                        :on-success="handleSuccess"
                        :on-cancel="() => handleClose(false)"
                    />
                </div>
            </SheetContent>
        </Sheet>
    </AppLayout>
</template>
