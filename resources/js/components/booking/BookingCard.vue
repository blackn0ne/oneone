<script setup lang="ts">
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { CalendarIcon, ClockIcon, UserIcon } from 'lucide-vue-next';
import { cn } from '@/lib/utils';

type BookingStatus = 'pending' | 'confirmed' | 'cancelled' | 'completed';

interface Props {
    id: string | number;
    serviceName: string;
    customerName: string;
    startTime: Date | string;
    endTime: Date | string;
    status: BookingStatus;
    price?: number;
    currency?: string;
}

const props = withDefaults(defineProps<Props>(), {
    currency: 'KZT',
});

const statusColors = {
    pending: 'bg-yellow-500/10 text-yellow-600 dark:text-yellow-400',
    confirmed: 'bg-green-500/10 text-green-600 dark:text-green-400',
    cancelled: 'bg-red-500/10 text-red-600 dark:text-red-400',
    completed: 'bg-blue-500/10 text-blue-600 dark:text-blue-400',
};

const statusLabels = {
    pending: 'Ожидает',
    confirmed: 'Подтверждено',
    cancelled: 'Отменено',
    completed: 'Завершено',
};

const formatDate = (date: Date | string) => {
    const d = typeof date === 'string' ? new Date(date) : date;
    return d.toLocaleDateString('ru-RU', {
        day: 'numeric',
        month: 'short',
        year: 'numeric',
    });
};

const formatTime = (date: Date | string) => {
    const d = typeof date === 'string' ? new Date(date) : date;
    return d.toLocaleTimeString('ru-RU', {
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-start justify-between">
                <div>
                    <CardTitle class="text-lg">{{ serviceName }}</CardTitle>
                    <CardDescription class="mt-1">
                        <div class="flex items-center gap-1">
                            <UserIcon class="h-3 w-3" />
                            {{ customerName }}
                        </div>
                    </CardDescription>
                </div>
                <Badge :class="cn('capitalize', statusColors[status])">
                    {{ statusLabels[status] }}
                </Badge>
            </div>
        </CardHeader>
        <CardContent>
            <div class="space-y-2">
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <CalendarIcon class="h-4 w-4" />
                    <span>{{ formatDate(startTime) }}</span>
                </div>
                <div class="flex items-center gap-2 text-sm text-muted-foreground">
                    <ClockIcon class="h-4 w-4" />
                    <span>{{ formatTime(startTime) }} - {{ formatTime(endTime) }}</span>
                </div>
                <div v-if="price" class="mt-3 text-lg font-semibold">
                    {{ price.toLocaleString('ru-RU') }} {{ currency }}
                </div>
            </div>
        </CardContent>
        <CardFooter class="flex gap-2">
            <Button variant="outline" size="sm" class="flex-1">
                Детали
            </Button>
            <Button v-if="status === 'pending'" variant="default" size="sm" class="flex-1">
                Подтвердить
            </Button>
        </CardFooter>
    </Card>
</template>
