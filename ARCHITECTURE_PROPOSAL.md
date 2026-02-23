# ONEONE - Архитектурное предложение и функции

## 📋 Обзор проекта

Мультиарендная SaaS-платформа для бронирования услуг, аналог BookingHub, разработанная на Laravel с архитектурой database-per-tenant.

---

## 🏗️ Архитектура базы данных

### Central Database (Центральная БД)

**Модели:**
- `Tenant` - клиенты платформы
- `Subscription` - подписки
- `Plan` - тарифные планы
- `Domain` - кастомные домены
- `Billing` - биллинг и платежи
- `GlobalSetting` - глобальные настройки
- `CentralUser` - администраторы платформы

### Tenant Database (База данных арендатора)

**Основные модели:**
- `Service` - услуги
- `Staff` - сотрудники
- `Booking` - бронирования
- `Customer` - клиенты арендатора
- `Schedule` - расписание
- `Location` - локации/филиалы
- `Extra` - дополнительные услуги
- `Package` - пакетные услуги
- `PromoCode` - промокоды
- `Refund` - возвраты
- `Payment` - платежи
- `Message` - сообщения (CRM)
- `Task` - задачи
- `Notification` - уведомления
- `Form` - формы бронирования
- `FormField` - поля форм

---

## 🎯 Режимы бронирования (Booking Modes)

### 1. Service Mode (Услуги)
- Бронирование услуг с временными слотами
- Назначение сотрудников
- Множественные локации
- Буферное время
- Время подготовки

### 2. Hotel Mode (Отели)
- Бронирование номеров
- Сезонное ценообразование
- Гибкие тарифы (weekend, holiday)
- Минимум/максимум ночей
- Дополнительные услуги (завтрак, парковка)
- Room ID для отслеживания

### 3. Event Mode (События)
- Регистрация на события
- Ограничение мест
- Типы билетов
- Ценовые уровни
- Организаторы
- Фильтры по комнатам

### 4. Online Meeting Mode (Онлайн встречи)
- Интеграция Zoom/Google Meet
- Автоматическая генерация ссылок
- Видео/аудио звонки
- Календарь встреч

### 5. Rental Mode (Аренда)
- Аренда транспорта/оборудования
- Выбор типа транспорта
- Период аренды
- Pick-up/Drop-off локации
- Дополнительное страхование
- Гибкие тарифы

### 6. Chauffeur Mode (Водитель/Такси)
- Мгновенные трансферы
- Запланированные поездки
- Обратные поездки
- Дистанционное ценообразование
- Ценообразование по времени
- Фиксированные тарифы
- Интеграция Google Maps

---

## ✨ Ключевые функции для реализации

### 🤖 AI Assistant (ИИ-ассистент)
- Чат-бот для бронирования
- Понимание потребностей клиента
- Проверка доступности в реальном времени
- Сбор данных для бронирования
- 24/7 онлайн поддержка
- **Требует:** OpenAI API Key

### 📅 Гибкие опции бронирования

#### Group Appointments (Групповые бронирования)
- Множественные участники
- Ограничение размера группы
- Цена за человека
- Идеально для: классы, воркшопы, туры

#### Multiple Appointments (Множественные бронирования)
- Несколько услуг в одном бронировании
- Пакетные бронирования
- Удобство для клиентов

#### Custom Duration (Кастомная длительность)
- Выбор длительности клиентом
- 30, 60, 90 минут и т.д.
- Гибкое расписание

#### Recurring Appointments (Повторяющиеся бронирования)
- Ежедневные/еженедельные/кастомные
- Для: терапия, репетиторство, фитнес

### 💰 Финансовые функции

#### Flexible Rates (Гибкие тарифы)
- Различные тарифы для номеров/событий/аренды
- Условия отмены
- Ценовые уровни
- Страховые планы

#### Cross-selling (Дополнительные услуги)
- Extras с управлением инвентарем
- Привязка к услугам
- Увеличение выручки

#### Bundled Services (Пакетные услуги)
- Объединение услуг в пакеты
- Скидки на пакеты
- Упрощение выбора

#### Promo Codes (Промокоды)
- Условия: минимальная сумма, даты, категории
- Целевые промоакции
- Планирование акций

#### Easy Refunds (Возвраты)
- Автоматические возвраты
- Настраиваемые условия
- Прозрачный статус для клиентов и админов

#### Payment Types (Типы платежей)
- Полная оплата
- Депозиты (процент или фиксированная сумма)
- Настраиваемые настройки

#### Catalog Mode (Режим каталога)
- Просмотр без оплаты
- Проверка доступности
- Без цен
- Для: врачи, стоматологи

#### Reward System (Система лояльности)
- Условные скидки
- По истории бронирований
- По способу оплаты
- По группам клиентов
- По деталям бронирования

### 🔧 Автоматизация

#### Inbox (Входящие)
- Централизованная коммуникация
- Клиенты и сотрудники
- Real-time уведомления
- Без внешних email

#### Staff Permissions (Права сотрудников)
- Роли: admin, manager, staff, customer
- Кастомные роли
- Гранулярные права доступа
- Предотвращение несанкционированных действий

#### Booking Rules (Правила бронирования)
- Отмена клиентом
- Перенос клиентом
- Повторные бронирования
- Автоматизация без ручного одобрения

### 📱 Мобильная оптимизация
- Mobile-first подход
- Адаптивный дизайн
- Оптимизация для всех устройств
- PWA поддержка (опционально)

### 🎨 Live Form Editor (Живой редактор форм)
- Создание форм в реальном времени
- Drag & drop интерфейс
- Персонализация
- Мобильная оптимизация
- Готовые шаблоны

### 🌐 Интеграции

#### Zapier
- Интеграция с 5000+ приложений
- Триггеры:
  - Новое бронирование
  - Добавление клиента
  - Перенос/отмена
  - Получение платежа
- Примеры:
  - Добавление в Mailchimp
  - Генерация инвойса в Xero
  - Создание клиента в HubSpot
  - Уведомление через Gmail
  - Возврат в Stripe

#### Zoom / Google Meet
- Автоматическая генерация ссылок
- Интеграция в бронирование
- Доставка ссылок клиентам и сотрудникам

#### Google Maps
- Для Chauffeur mode
- Расчет расстояния
- Маршруты

### 🌍 Мультиязычность
- WPML совместимость (для WordPress интеграции)
- i18n поддержка
- Переводы интерфейса
- Переводы форм

### 🔒 GDPR Compliance
- Уведомления о данных
- Согласие перед сбором
- Ссылки на Privacy Policy
- В AI Chatbot

### 📊 Аналитика и отчетность
- Дашборды
- Статистика бронирований
- Финансовые отчеты
- Отчеты по сотрудникам
- Отчеты по клиентам

---

## 📁 Предлагаемая структура проекта

```
app/
├── Models/
│   ├── Central/
│   │   ├── Tenant.php
│   │   ├── Subscription.php
│   │   ├── Plan.php
│   │   ├── Domain.php
│   │   ├── Billing.php
│   │   ├── GlobalSetting.php
│   │   └── CentralUser.php
│   └── Tenant/
│       ├── Service.php
│       ├── Staff.php
│       ├── Booking.php
│       ├── Customer.php
│       ├── Schedule.php
│       ├── Location.php
│       ├── Extra.php
│       ├── Package.php
│       ├── PromoCode.php
│       ├── Refund.php
│       ├── Payment.php
│       ├── Message.php
│       ├── Task.php
│       ├── Notification.php
│       ├── Form.php
│       ├── FormField.php
│       └── BookingMode.php (enum)
│
├── Http/
│   ├── Controllers/
│   │   ├── Central/
│   │   │   ├── TenantController.php
│   │   │   ├── SubscriptionController.php
│   │   │   ├── BillingController.php
│   │   │   └── DashboardController.php
│   │   └── Tenant/
│   │       ├── BookingController.php
│   │       ├── ServiceController.php
│   │       ├── StaffController.php
│   │       ├── CustomerController.php
│   │       ├── ScheduleController.php
│   │       ├── FormController.php
│   │       ├── PaymentController.php
│   │       ├── MessageController.php
│   │       ├── AIController.php
│   │       └── DashboardController.php
│   │
│   ├── Middleware/
│   │   ├── IdentifyTenant.php
│   │   ├── SetTenantConnection.php
│   │   └── TenantAuth.php
│   │
│   └── Requests/
│       ├── Central/
│       └── Tenant/
│
├── Services/
│   ├── TenantService.php
│   ├── BookingService.php
│   ├── PaymentService.php
│   ├── AIService.php
│   ├── NotificationService.php
│   ├── IntegrationService.php
│   └── FormBuilderService.php
│
├── Jobs/
│   ├── SendBookingNotification.php
│   ├── ProcessRefund.php
│   ├── GenerateMeetingLink.php
│   └── SyncWithZapier.php
│
└── Events/
    ├── BookingCreated.php
    ├── BookingCancelled.php
    ├── PaymentReceived.php
    └── TenantCreated.php

database/
├── migrations/
│   ├── central/
│   │   ├── 0001_create_tenants_table.php
│   │   ├── 0002_create_subscriptions_table.php
│   │   ├── 0003_create_plans_table.php
│   │   ├── 0004_create_domains_table.php
│   │   ├── 0005_create_billings_table.php
│   │   └── 0006_create_global_settings_table.php
│   │
│   └── tenant/
│       ├── 0001_create_services_table.php
│       ├── 0002_create_staff_table.php
│       ├── 0003_create_bookings_table.php
│       ├── 0004_create_customers_table.php
│       ├── 0005_create_schedules_table.php
│       ├── 0006_create_locations_table.php
│       ├── 0007_create_extras_table.php
│       ├── 0008_create_packages_table.php
│       ├── 0009_create_promo_codes_table.php
│       ├── 0010_create_refunds_table.php
│       ├── 0011_create_payments_table.php
│       ├── 0012_create_messages_table.php
│       ├── 0013_create_tasks_table.php
│       ├── 0014_create_notifications_table.php
│       ├── 0015_create_forms_table.php
│       └── 0016_create_form_fields_table.php

config/
├── tenancy.php
└── booking.php
```

---

## 🚀 Дополнительные предложения

### 1. Real-time обновления
- WebSockets (Laravel Echo + Pusher/Soketi)
- Live обновления бронирований
- Real-time уведомления
- Live чат

### 2. Календарь
- Полнофункциональный календарь
- Просмотр/редактирование бронирований
- Drag & drop для переноса
- Фильтры по сотрудникам/услугам

### 3. Email/SMS уведомления
- Напоминания о бронированиях
- Подтверждения
- Уведомления об изменениях
- Интеграция с Twilio, SendGrid

### 4. Экспорт данных
- Экспорт в CSV/Excel
- PDF отчеты
- Инвойсы
- Календарные файлы (.ics)

### 5. API для интеграций
- RESTful API
- GraphQL (опционально)
- Webhooks
- API документация (Swagger/OpenAPI)

### 6. Белый лейбл
- Кастомизация брендинга
- Собственные домены
- Кастомные email шаблоны
- Логотипы и цвета

### 7. Marketplace для аддонов
- Расширяемая архитектура
- Плагины/аддоны
- Монетизация экосистемы

### 8. Advanced Analytics
- Прогнозирование
- Рекомендации по ценообразованию
- Анализ клиентов
- A/B тестирование форм

### 9. Multi-currency
- Поддержка валют
- Автоконвертация
- Локальные платежи

### 10. Backup & Recovery
- Автоматические бэкапы
- Восстановление данных
- Версионирование

---

## 🛠️ Технические рекомендации

### Пакеты Laravel для рассмотрения:
- `stancl/tenancy` - для мультитенантности
- `spatie/laravel-permission` - для ролей и прав
- `laravel/cashier` - для подписок и платежей
- `spatie/laravel-activitylog` - для логирования
- `spatie/laravel-query-builder` - для фильтрации
- `maatwebsite/excel` - для экспорта
- `barryvdh/laravel-dompdf` - для PDF
- `laravel/horizon` - для очередей
- `pusher/pusher-php-server` - для WebSockets

### Frontend:
- Inertia.js (уже используется)
- Vue 3 (уже используется)
- Tailwind CSS
- Vue Calendar компоненты
- Chart.js для аналитики

---

## 📝 Приоритеты разработки

### Phase 1 (MVP)
1. Мультитенантность (database-per-tenant)
2. Базовые модели (Service, Staff, Booking, Customer)
3. Service Mode бронирования
4. Базовый дашборд
5. Аутентификация для tenant

### Phase 2
1. Остальные режимы бронирования
2. Платежи и депозиты
3. Уведомления
4. Базовые формы

### Phase 3
1. AI Assistant
2. Интеграции (Zapier, Zoom, Google Meet)
3. Продвинутые функции (Extras, Packages, Promo Codes)
4. Live Form Editor

### Phase 4
1. Аналитика
2. Marketplace
3. Белый лейбл
4. Оптимизация и масштабирование

---

## 💡 Уникальные предложения для ONEONE

1. **Гибкая система тарификации** - более продвинутая, чем у конкурентов
2. **Российская локализация** - полная поддержка русского языка и платежных систем
3. **Интеграция с российскими сервисами** - Яндекс.Календарь, СМС.ru, и т.д.
4. **Модульная архитектура** - легко расширяемая система
5. **Open Source core** - возможность открыть базовую версию

---

Этот документ служит основой для планирования и разработки проекта ONEONE.
