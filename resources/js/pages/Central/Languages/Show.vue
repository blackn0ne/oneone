<script setup lang="ts">
import { Head, useForm, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardFooter, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Badge } from '@/components/ui/badge';
import { route } from '@/lib/routes';
import { ref } from 'vue';

interface Language {
    id: number;
    name: string;
    code: string;
    is_active: boolean;
    sort_order: number;
}

interface Props {
    language: Language;
    translations: Record<string, string>;
}

const props = defineProps<Props>();

const translationsForm = useForm({
    translations: props.translations || {},
});

const newKey = ref('');
const newValue = ref('');

const addTranslation = () => {
    if (newKey.value && newValue.value) {
        translationsForm.translations[newKey.value] = newValue.value;
        newKey.value = '';
        newValue.value = '';
    }
};

const removeTranslation = (key: string) => {
    delete translationsForm.translations[key];
};

const submitTranslations = () => {
    translationsForm.post(route('central.languages.translations.update', props.language.id));
};
</script>

<template>
    <Head :title="`Язык: ${props.language.name}`" />

    <AppLayout>
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">{{ props.language.name }}</h1>
                    <p class="text-muted-foreground">
                        Код: {{ props.language.code }} | Файл: {{ props.language.code }}.json
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="route('central.languages.edit', props.language.id)">
                        <Button variant="outline">Редактировать</Button>
                    </Link>
                    <Link :href="route('central.languages.index')">
                        <Button variant="outline">Назад</Button>
                    </Link>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Информация о языке -->
                <Card>
                    <CardHeader>
                        <CardTitle>Информация</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <Label>Название</Label>
                            <p class="text-sm font-medium">{{ props.language.name }}</p>
                        </div>
                        <div>
                            <Label>Код</Label>
                            <p class="text-sm font-medium">{{ props.language.code }}</p>
                        </div>
                        <div>
                            <Label>Статус</Label>
                            <div class="mt-1">
                                <Badge :variant="props.language.is_active ? 'default' : 'secondary'">
                                    {{ props.language.is_active ? 'Активен' : 'Неактивен' }}
                                </Badge>
                            </div>
                        </div>
                        <div>
                            <Label>Порядок сортировки</Label>
                            <p class="text-sm font-medium">{{ props.language.sort_order }}</p>
                        </div>
                    </CardContent>
                </Card>

                <!-- Переводы -->
                <Card>
                    <CardHeader>
                        <CardTitle>Переводы ({{ Object.keys(translationsForm.translations).length }})</CardTitle>
                        <CardDescription>
                            Управление переводами для файла {{ props.language.code }}.json
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <Label>Добавить перевод</Label>
                            <div class="grid gap-2 md:grid-cols-2">
                                <Input
                                    v-model="newKey"
                                    placeholder="Ключ (например: welcome)"
                                />
                                <Input
                                    v-model="newValue"
                                    placeholder="Значение (например: Добро пожаловать)"
                                />
                            </div>
                            <Button type="button" size="sm" @click="addTranslation">
                                Добавить
                            </Button>
                        </div>

                        <div class="max-h-96 space-y-2 overflow-y-auto">
                            <div
                                v-for="(value, key) in translationsForm.translations"
                                :key="key"
                                class="flex items-center gap-2 rounded border p-2"
                            >
                                <div class="flex-1">
                                    <p class="text-xs font-mono text-muted-foreground">{{ key }}</p>
                                    <p class="text-sm">{{ value }}</p>
                                </div>
                                <Button
                                    type="button"
                                    variant="ghost"
                                    size="sm"
                                    @click="removeTranslation(key)"
                                >
                                    Удалить
                                </Button>
                            </div>
                            <p v-if="Object.keys(translationsForm.translations).length === 0" class="text-center text-sm text-muted-foreground">
                                Нет переводов
                            </p>
                        </div>
                    </CardContent>
                    <CardFooter>
                        <Button
                            type="button"
                            :disabled="translationsForm.processing"
                            @click="submitTranslations"
                        >
                            {{ translationsForm.processing ? 'Сохранение...' : 'Сохранить переводы' }}
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
