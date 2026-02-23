# shadcn-vue - Установка и использование

## ✅ Что уже сделано

### 1. Установлены компоненты shadcn-vue

Все необходимые компоненты для системы бронирования установлены:

- ✅ **Table** - таблицы
- ✅ **Form** - формы с валидацией
- ✅ **Select** - выпадающие списки
- ✅ **Calendar** - календарь
- ✅ **Textarea** - многострочный ввод
- ✅ **Tabs** - вкладки
- ✅ **Progress** - прогресс-бары
- ✅ **Sonner** (Toast) - уведомления
- ✅ **Popover** - всплывающие окна
- ✅ **Combobox** - автодополнение
- ✅ **Card, Button, Badge, Input, Label, Checkbox** - базовые компоненты

### 2. Созданы компоненты для бронирования

- `BookingCard.vue` - карточка бронирования
- `ServiceCard.vue` - карточка услуги
- `BookingCalendar.vue` - календарь для выбора даты
- `BookingForm.vue` - форма создания бронирования

### 3. Обновлен Dashboard

Dashboard полностью переделан на компоненты shadcn-vue с:
- Статистическими карточками
- Таблицей бронирований
- Вкладками (Бронирования, Услуги, Календарь)
- Карточками услуг и бронирований

### 4. Добавлен Toaster

Toaster для уведомлений добавлен в `AppLayout.vue`

## 📦 Установка новых компонентов

Для установки новых компонентов используйте:

```bash
npx shadcn-vue@latest add [component-name]
```

Примеры:
```bash
npx shadcn-vue@latest add dialog
npx shadcn-vue@latest add alert-dialog
npx shadcn-vue@latest add slider
npx shadcn-vue@latest add switch
```

## 🎨 Использование компонентов

### Пример использования Toast

```vue
<script setup>
import { toast } from 'vue-sonner';

const showSuccess = () => {
    toast.success('Операция выполнена успешно!');
};

const showError = () => {
    toast.error('Произошла ошибка');
};
</script>
```

### Пример использования Form

```vue
<script setup>
import { useForm } from '@inertiajs/vue3';
import { Form, FormField, FormItem, FormLabel, FormControl, FormMessage } from '@/components/ui/form';
import { Input } from '@/components/ui/input';
import { Button } from '@/components/ui/button';

const form = useForm({
    name: '',
    email: '',
});

const submit = () => {
    form.post('/submit');
};
</script>

<template>
    <Form @submit.prevent="submit">
        <FormField v-model="form.name" name="name">
            <FormItem>
                <FormLabel>Имя</FormLabel>
                <FormControl>
                    <Input v-model="form.name" />
                </FormControl>
                <FormMessage />
            </FormItem>
        </FormField>
        <Button type="submit">Отправить</Button>
    </Form>
</template>
```

### Пример использования Table

```vue
<script setup>
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const bookings = [
    { id: 1, service: 'Массаж', customer: 'Иван Иванов', date: '2025-02-23' },
    { id: 2, service: 'Стрижка', customer: 'Мария Петрова', date: '2025-02-24' },
];
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>Услуга</TableHead>
                <TableHead>Клиент</TableHead>
                <TableHead>Дата</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="booking in bookings" :key="booking.id">
                <TableCell>{{ booking.service }}</TableCell>
                <TableCell>{{ booking.customer }}</TableCell>
                <TableCell>{{ booking.date }}</TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
```

## 🎯 Следующие шаги

1. **Создать страницы для управления:**
   - Страница услуг (Services)
   - Страница сотрудников (Staff)
   - Страница клиентов (Customers)
   - Страница бронирований (Bookings)

2. **Добавить больше компонентов:**
   - Dialog для модальных окон
   - Alert Dialog для подтверждений
   - Data Table для расширенных таблиц
   - Charts для аналитики

3. **Создать формы:**
   - Форма создания услуги
   - Форма создания сотрудника
   - Форма редактирования бронирования

## 📚 Документация

- [shadcn-vue Documentation](https://www.shadcn-vue.com/)
- [Компоненты](https://www.shadcn-vue.com/docs/components)
- [Примеры](https://www.shadcn-vue.com/docs/examples)

## 🔧 Конфигурация

Конфигурация находится в `components.json`:

```json
{
    "style": "new-york-v4",
    "tailwind": {
        "config": "tailwind.config.js",
        "css": "resources/css/app.css",
        "baseColor": "neutral",
        "cssVariables": true
    },
    "aliases": {
        "components": "@/components",
        "utils": "@/lib/utils",
        "ui": "@/components/ui"
    }
}
```

## 💡 Советы

1. **Используйте утилиту `cn()`** для объединения классов:
   ```vue
   <div :class="cn('base-class', conditionalClass && 'conditional-class')">
   ```

2. **Импортируйте компоненты из `@/components/ui`**:
   ```vue
   import { Button } from '@/components/ui/button';
   ```

3. **Используйте TypeScript** для лучшей типизации

4. **Следуйте паттернам shadcn-vue** для консистентности
