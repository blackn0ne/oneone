# ✅ shadcn-vue - Установка завершена!

## 🎉 Что сделано

### 1. Установлены все необходимые компоненты shadcn-vue

✅ **Базовые компоненты:**
- Button, Card, Badge, Input, Label, Checkbox
- Separator, Skeleton, Spinner

✅ **Формы и ввод:**
- Form (с валидацией)
- Select, Textarea
- Input OTP
- Combobox

✅ **Навигация и структура:**
- Tabs
- Breadcrumb
- Navigation Menu
- Sidebar

✅ **Диалоги и всплывающие окна:**
- Dialog
- Sheet
- Popover
- Alert Dialog

✅ **Данные:**
- Table
- Calendar
- Progress

✅ **Уведомления:**
- Sonner (Toast)

✅ **Дополнительно:**
- Tooltip
- Collapsible
- Avatar

### 2. Созданы компоненты для системы бронирования

📦 **`resources/js/components/booking/`**

- **BookingCard.vue** - карточка бронирования с информацией
- **ServiceCard.vue** - карточка услуги
- **BookingCalendar.vue** - календарь для выбора даты
- **BookingForm.vue** - полная форма создания бронирования

### 3. Переделан Dashboard

📄 **`resources/js/pages/Dashboard.vue`**

Полностью переделан с использованием компонентов shadcn-vue:
- Статистические карточки (4 метрики)
- Таблица бронирований
- Вкладки (Бронирования, Услуги, Календарь)
- Карточки услуг и бронирований
- Современный дизайн

### 4. Интеграция Toaster

✅ Toaster добавлен в `AppLayout.vue` для глобальных уведомлений

## 🚀 Как использовать

### Запуск проекта

```bash
npm run dev
```

### Использование компонентов

Все компоненты доступны из `@/components/ui`:

```vue
<script setup>
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { toast } from 'vue-sonner';
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Заголовок</CardTitle>
        </CardHeader>
        <CardContent>
            <Button @click="toast.success('Привет!')">
                Нажми меня
            </Button>
        </CardContent>
    </Card>
</template>
```

### Примеры использования

#### Toast уведомления

```vue
<script setup>
import { toast } from 'vue-sonner';

const handleSuccess = () => {
    toast.success('Операция выполнена успешно!');
};

const handleError = () => {
    toast.error('Произошла ошибка');
};

const handleInfo = () => {
    toast.info('Информационное сообщение');
};
</script>
```

#### Форма с валидацией

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
    form.post('/submit', {
        onSuccess: () => toast.success('Успешно!'),
    });
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
        <Button type="submit" :disabled="form.processing">
            Отправить
        </Button>
    </Form>
</template>
```

#### Таблица данных

```vue
<script setup>
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';

const data = [
    { id: 1, name: 'Иван', email: 'ivan@example.com' },
    { id: 2, name: 'Мария', email: 'maria@example.com' },
];
</script>

<template>
    <Table>
        <TableHeader>
            <TableRow>
                <TableHead>Имя</TableHead>
                <TableHead>Email</TableHead>
            </TableRow>
        </TableHeader>
        <TableBody>
            <TableRow v-for="item in data" :key="item.id">
                <TableCell>{{ item.name }}</TableCell>
                <TableCell>{{ item.email }}</TableCell>
            </TableRow>
        </TableBody>
    </Table>
</template>
```

## 📁 Структура компонентов

```
resources/js/
├── components/
│   ├── ui/              # Компоненты shadcn-vue
│   │   ├── button/
│   │   ├── card/
│   │   ├── table/
│   │   ├── form/
│   │   └── ...
│   └── booking/         # Компоненты для бронирования
│       ├── BookingCard.vue
│       ├── ServiceCard.vue
│       ├── BookingCalendar.vue
│       └── BookingForm.vue
└── pages/
    └── Dashboard.vue    # Обновленный дашборд
```

## 🎨 Стилизация

Все компоненты используют:
- **Tailwind CSS 4** для стилей
- **CSS Variables** для темизации
- **Dark mode** поддержка
- **Responsive design** из коробки

Цветовая схема настраивается через CSS переменные в `resources/css/app.css`.

## 📚 Документация

- [shadcn-vue Docs](https://www.shadcn-vue.com/)
- [Компоненты](https://www.shadcn-vue.com/docs/components)
- [Примеры](https://www.shadcn-vue.com/docs/examples)

## 🔧 Установка новых компонентов

```bash
npx shadcn-vue@latest add [component-name]
```

Примеры:
```bash
npx shadcn-vue@latest add dialog
npx shadcn-vue@latest add alert-dialog
npx shadcn-vue@latest add slider
npx shadcn-vue@latest add switch
npx shadcn-vue@latest add radio-group
```

## 💡 Советы

1. **Используйте `cn()` утилиту** для объединения классов:
   ```vue
   <div :class="cn('base-class', isActive && 'active-class')">
   ```

2. **Импортируйте из `@/components/ui`**:
   ```vue
   import { Button } from '@/components/ui/button';
   ```

3. **Используйте TypeScript** для типизации

4. **Следуйте паттернам shadcn-vue** для консистентности

## 🎯 Следующие шаги

1. Создать страницы для:
   - Управления услугами
   - Управления сотрудниками
   - Управления клиентами
   - Детального просмотра бронирований

2. Добавить больше компонентов:
   - Data Table (расширенная таблица)
   - Charts (графики для аналитики)
   - Command (поиск команд)

3. Создать формы:
   - Создание/редактирование услуги
   - Создание/редактирование сотрудника
   - Редактирование бронирования

4. Добавить валидацию форм с помощью VeeValidate или TanStack Form

## ✨ Готово к использованию!

Все компоненты shadcn-vue установлены и готовы к использованию. Интерфейс полностью переделан на современные компоненты shadcn-vue.
