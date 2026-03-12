<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { ClockIcon, UsersIcon } from 'lucide-vue-next';

interface Props {
    id: string | number;
    name: string;
    description?: string;
    duration: number; // в минутах
    price: number;
    currency?: string;
    maxParticipants?: number;
    isActive?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    currency: 'KZT',
    isActive: true,
});

const formatDuration = (minutes: number) => {
    const hours = Math.floor(minutes / 60);
    const mins = minutes % 60;
    if (hours > 0) {
        return `${hours} ч ${mins > 0 ? mins + ' мин' : ''}`;
    }
    return `${mins} мин`;
};

const emit = defineEmits<{
    book: [id: string | number];
}>();
</script>

<template>
    <Card :class="{ 'opacity-60': !isActive }">
        <CardHeader>
            <div class="flex items-start justify-between">
                <div class="flex-1">
                    <CardTitle>{{ name }}</CardTitle>
                    <CardDescription v-if="description" class="mt-2">
                        {{ description }}
                    </CardDescription>
                </div>
                <Badge v-if="!isActive" variant="secondary">Недоступно</Badge>
            </div>
        </CardHeader>
        <CardContent>
            <div class="space-y-3">
                <div class="flex items-center gap-4 text-sm text-muted-foreground">
                    <div class="flex items-center gap-1">
                        <ClockIcon class="h-4 w-4" />
                        <span>{{ formatDuration(duration) }}</span>
                    </div>
                    <div v-if="maxParticipants" class="flex items-center gap-1">
                        <UsersIcon class="h-4 w-4" />
                        <span>До {{ maxParticipants }} чел.</span>
                    </div>
                </div>
                <div class="text-2xl font-bold">
                    {{ price.toLocaleString('ru-RU') }} {{ currency }}
                </div>
            </div>
        </CardContent>
        <CardFooter>
            <Button 
                :disabled="!isActive" 
                class="w-full" 
                @click="emit('book', id)"
            >
                Забронировать
            </Button>
        </CardFooter>
    </Card>
</template>
