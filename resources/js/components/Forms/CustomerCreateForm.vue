<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { route } from '@/lib/routes';

interface Props {
    onSuccess?: () => void;
    onCancel?: () => void;
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    email: '',
    phone: '',
    address: '',
    notes: '',
});

const submit = () => {
    form.post(route('customers.store'), {
        onSuccess: () => {
            form.reset();
            props.onSuccess?.();
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="space-y-4">
        <div class="grid gap-4 md:grid-cols-2">
            <div class="space-y-2">
                <Label for="name">Имя *</Label>
                <Input id="name" v-model="form.name" required />
                <p v-if="form.errors.name" class="text-sm text-destructive">
                    {{ form.errors.name }}
                </p>
            </div>

            <div class="space-y-2">
                <Label for="email">Email</Label>
                <Input id="email" v-model="form.email" type="email" />
                <p v-if="form.errors.email" class="text-sm text-destructive">
                    {{ form.errors.email }}
                </p>
            </div>
        </div>

        <div class="space-y-2">
            <Label for="phone">Телефон</Label>
            <Input id="phone" v-model="form.phone" type="tel" />
            <p v-if="form.errors.phone" class="text-sm text-destructive">
                {{ form.errors.phone }}
            </p>
        </div>

        <div class="space-y-2">
            <Label for="address">Адрес</Label>
            <Input id="address" v-model="form.address" />
            <p v-if="form.errors.address" class="text-sm text-destructive">
                {{ form.errors.address }}
            </p>
        </div>

        <div class="space-y-2">
            <Label for="notes">Заметки</Label>
            <Textarea id="notes" v-model="form.notes" rows="4" />
            <p v-if="form.errors.notes" class="text-sm text-destructive">
                {{ form.errors.notes }}
            </p>
        </div>

        <div class="flex justify-end gap-2 pt-4">
            <Button type="button" variant="outline" @click="props.onCancel">
                Отмена
            </Button>
            <Button type="submit" :disabled="form.processing">
                {{ form.processing ? 'Создание...' : 'Создать клиента' }}
            </Button>
        </div>
    </form>
</template>
