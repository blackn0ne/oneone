<script setup lang="ts">
import { watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { route } from '@/lib/routes';

interface Props {
    business?: {
        id?: number;
        name?: string;
        address?: string;
        phone?: string;
        email?: string;
        is_active?: boolean;
    };
    onSuccess?: () => void;
    onCancel?: () => void;
}

const props = defineProps<Props>();

const form = useForm({
    name: props.business?.name || '',
    address: props.business?.address || '',
    phone: props.business?.phone || '',
    email: props.business?.email || '',
    is_active: props.business?.is_active ?? true,
});

watch(() => props.business, () => {
    if (props.business) {
        form.name = props.business.name || '';
        form.address = props.business.address || '';
        form.phone = props.business.phone || '';
        form.email = props.business.email || '';
        form.is_active = props.business.is_active ?? true;
    }
}, { deep: true, immediate: true });

const submit = () => {
    if (props.business?.id) {
        form.put(route('business.update', props.business.id), {
            onSuccess: () => {
                props.onSuccess?.();
            },
        });
    } else {
        form.post(route('business.store'), {
            onSuccess: () => {
                form.reset();
                props.onSuccess?.();
            },
        });
    }
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-6">
        <div class="space-y-4">
            <div class="grid gap-4 md:grid-cols-2">
                <div class="space-y-2">
                    <Label for="name">Название точки продаж *</Label>
                    <Input id="name" v-model="form.name" required />
                    <p v-if="form.errors.name" class="text-sm text-destructive">
                        {{ form.errors.name }}
                    </p>
                </div>

                <div class="space-y-2">
                    <Label for="phone">Телефон</Label>
                    <Input id="phone" v-model="form.phone" type="tel" />
                    <p v-if="form.errors.phone" class="text-sm text-destructive">
                        {{ form.errors.phone }}
                    </p>
                </div>
            </div>

            <div class="space-y-2">
                <Label for="address">Адрес</Label>
                <Textarea id="address" v-model="form.address" rows="2" />
                <p v-if="form.errors.address" class="text-sm text-destructive">
                    {{ form.errors.address }}
                </p>
            </div>

            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" />
                <p v-if="form.errors.email" class="text-sm text-destructive">
                    {{ form.errors.email }}
                </p>
            </div>

            <div class="flex items-center space-x-2">
                <Checkbox id="is_active" v-model="form.is_active" />
                <Label for="is_active" class="font-medium cursor-pointer">
                    Активна
                </Label>
            </div>
        </div>


        <div class="flex justify-end gap-2">
            <Button type="button" variant="outline" @click="props.onCancel">
                Отмена
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Сохранение...' : (props.business?.id ? 'Обновить' : 'Создать') }}
            </Button>
        </div>
    </form>
</template>
