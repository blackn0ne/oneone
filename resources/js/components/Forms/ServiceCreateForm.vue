<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { Checkbox } from '@/components/ui/checkbox';
import { SheetFooter } from '@/components/ui/sheet';
import type { Business } from '@/types';
import { route } from '@/lib/routes';

interface Props {
    businesses: Business[];
    onSuccess?: () => void;
    onCancel?: () => void;
}

const props = defineProps<Props>();

const form = useForm({
    name: '',
    description: '',
    duration: 60,
    price: 0,
    business_id: '',
    is_active: true,
    booking_mode: 'service',
});

const submit = () => {
    form.post(route('services.store'), {
        onSuccess: () => {
            form.reset();
            props.onSuccess?.();
        },
    });
};
</script>

<template>
    <form @submit.prevent="submit" class="flex flex-col space-y-6 h-full">
        <div class="px-4">
            <div class="grid gap-4">
                
                    <div class="space-y-2">
                        <Label for="name">Название *</Label>
                        <Input id="name" v-model="form.name" class="w-full" required />
                        <p v-if="form.errors.name" class="text-sm text-destructive mt-1">
                            {{ form.errors.name }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="price">Цена *</Label>
                        <Input id="price" v-model.number="form.price" type="number" min="0" step="0.01" class="w-full" required />
                        <p v-if="form.errors.price" class="text-sm text-destructive mt-1">
                            {{ form.errors.price }}
                        </p>
                    </div>
                

                    <div class="space-y-2">
                        <Label for="description">Описание</Label>
                        <Textarea id="description" v-model="form.description" rows="3" class="w-full resize-none" />
                    </div>

                
                    <div class="space-y-2">
                        <Label for="duration">Длительность (мин) *</Label>
                        <Input id="duration" v-model.number="form.duration" type="number" min="1" class="w-full" required />
                        <p v-if="form.errors.duration" class="text-sm text-destructive mt-1">
                            {{ form.errors.duration }}
                        </p>
                    </div>

                    
               

                
                    <div class="space-y-2">
                        <Label for="booking_mode">Режим бронирования *</Label>
                        <Select v-model="form.booking_mode" required>
                            <SelectTrigger id="booking_mode" class="w-full">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="service">Услуга</SelectItem>
                                <SelectItem value="hotel">Отель</SelectItem>
                                <SelectItem value="event">Событие</SelectItem>
                                <SelectItem value="online">Онлайн</SelectItem>
                                <SelectItem value="rental">Аренда</SelectItem>
                                <SelectItem value="chauffeur">Водитель</SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.booking_mode" class="text-sm text-destructive mt-1">
                            {{ form.errors.booking_mode }}
                        </p>
                    </div>

                    <div class="space-y-2">
                        <Label for="business">Точка продаж</Label>
                        <Select v-model="form.business_id">
                            <SelectTrigger id="business" class="w-full">
                                <SelectValue placeholder="Выберите точку продаж (опционально)" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem
                                    v-for="business in businesses"
                                    :key="business.id"
                                    :value="String(business.id)"
                                >
                                    {{ business.name }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <p v-if="form.errors.business_id" class="text-sm text-destructive mt-1">
                            {{ form.errors.business_id }}
                        </p>
                    </div>
                

                    <div class="flex items-center space-x-2">
                        <Checkbox id="is_active" v-model="form.is_active" />
                        <Label for="is_active" class="cursor-pointer">Активна</Label>
                    </div>
            </div>
        </div>

        <SheetFooter class="border-t p-4 mt-auto">
            <Button type="submit" :disabled="form.processing" class="w-full">
                {{ form.processing ? 'Создание...' : 'Создать услугу' }}
            </Button>
        </SheetFooter>
    </form>
</template>
