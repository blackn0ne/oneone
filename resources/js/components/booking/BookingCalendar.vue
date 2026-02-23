<script setup lang="ts">
import { Calendar } from '@/components/ui/calendar';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { Button } from '@/components/ui/button';
import { CalendarIcon } from 'lucide-vue-next';
import { cn } from '@/lib/utils';
import { ref } from 'vue';
// import { format } from 'date-fns';
// import { ru } from 'date-fns/locale';

const date = ref<Date>();

const emit = defineEmits<{
    'update:date': [date: Date | undefined];
}>();

const handleDateSelect = (selectedDate: Date | undefined) => {
    date.value = selectedDate;
    emit('update:date', selectedDate);
};
</script>

<template>
    <Popover>
        <PopoverTrigger as-child>
            <Button
                variant="outline"
                :class="cn(
                    'w-full justify-start text-left font-normal',
                    !date && 'text-muted-foreground'
                )"
            >
                <CalendarIcon class="mr-2 h-4 w-4" />
                <span v-if="date">
                    {{ date.toLocaleDateString('ru-RU', { day: 'numeric', month: 'long', year: 'numeric' }) }}
                </span>
                <span v-else>Выберите дату</span>
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <Calendar
                v-model="date"
                @update:model-value="handleDateSelect"
            />
        </PopoverContent>
    </Popover>
</template>
