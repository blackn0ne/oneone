# Структура кода проекта ONEONE

## 📁 Организация файлов

### Backend (Laravel)

```
app/
├── Enums/                          # Enum классы для статусов
│   ├── TenantStatus.php
│   ├── BookingStatus.php
│   └── SubscriptionStatus.php
│
├── Http/
│   ├── Controllers/
│   │   ├── Central/               # Контроллеры центрального приложения
│   │   │   ├── DashboardController.php
│   │   │   ├── TenantController.php
│   │   │   ├── PlanController.php
│   │   │   └── SubscriptionController.php
│   │   ├── Tenant/                 # Контроллеры tenant приложения
│   │   │   ├── DashboardController.php
│   │   │   ├── BookingController.php
│   │   │   ├── ServiceController.php
│   │   │   ├── StaffController.php
│   │   │   └── CustomerController.php
│   │   └── Settings/              # Настройки пользователя
│   │
│   ├── Middleware/
│   │   ├── EnsureSuperAdmin.php
│   │   ├── HandleInertiaRequests.php
│   │   └── RoleMiddleware.php
│   │
│   └── Requests/
│       └── Central/                # Form Request классы
│           ├── StoreTenantRequest.php
│           ├── UpdateTenantRequest.php
│           ├── StorePlanRequest.php
│           └── UpdatePlanRequest.php
│
├── Models/
│   ├── Central/                    # Модели центральной БД
│   │   ├── Tenant.php
│   │   ├── Plan.php
│   │   ├── Subscription.php
│   │   ├── Domain.php
│   │   ├── Billing.php
│   │   └── CentralUser.php
│   ├── Tenant/                     # Модели tenant БД
│   │   ├── Booking.php
│   │   ├── Service.php
│   │   ├── Staff.php
│   │   ├── Customer.php
│   │   └── Location.php
│   └── User.php                    # Модель пользователя
│
└── Services/                        # Сервисы для бизнес-логики
    ├── Central/
    │   └── TenantService.php
    └── BookingService.php
```

### Frontend (Vue/Inertia)

```
resources/js/
├── components/
│   ├── ui/                         # shadcn-vue компоненты
│   ├── booking/                    # Компоненты бронирований
│   ├── AppSidebar.vue
│   ├── AppHeader.vue
│   └── ...
│
├── layouts/
│   └── AppLayout.vue
│
├── pages/
│   ├── Dashboard.vue
│   ├── Bookings/
│   ├── Services/
│   ├── Staff/
│   ├── Customers/
│   └── Central/
│       ├── Dashboard.vue
│       ├── Tenants/
│       ├── Plans/
│       └── Subscriptions/
│
├── types/
│   ├── auth.ts
│   ├── booking.ts
│   └── ...
│
└── lib/
    ├── routes.ts
    └── utils.ts
```

### Database

```
database/
├── migrations/
│   ├── central/                    # Миграции центральной БД
│   │   ├── create_plans_table.php
│   │   ├── create_subscriptions_table.php
│   │   └── create_billings_table.php
│   ├── tenant/                     # Миграции tenant БД
│   │   ├── create_locations_table.php
│   │   ├── create_services_table.php
│   │   ├── create_staff_table.php
│   │   ├── create_customers_table.php
│   │   ├── create_bookings_table.php
│   │   └── create_service_staff_table.php
│   └── ...                         # Общие миграции
│
└── seeders/
    ├── RolePermissionSeeder.php
    ├── CentralUserSeeder.php
    └── DatabaseSeeder.php
```

## 🎯 Принципы организации

### 1. Разделение по контекстам
- **Central/** - код для центрального приложения (суперадмин)
- **Tenant/** - код для tenant приложения (клиенты платформы)

### 2. Использование Enums
- Все статусы вынесены в Enum классы
- Enum содержит методы `label()` и `color()` для UI

### 3. Form Requests
- Валидация вынесена в отдельные Request классы
- Автоматическая проверка прав доступа

### 4. Service Layer
- Бизнес-логика вынесена в Service классы
- Контроллеры остаются тонкими

### 5. PHPDoc комментарии
- Все классы и методы документированы
- Указаны типы параметров и возвращаемых значений

### 6. Типизация
- Строгая типизация в PHP (type hints)
- TypeScript типы для фронтенда

## 📝 Стандарты кода

### Контроллеры
- Все методы имеют PHPDoc
- Используются Form Requests для валидации
- Возвращаемые типы указаны явно
- Единообразная структура методов

### Модели
- PHPDoc с описанием свойств
- Использование Enums для статусов
- Методы-помощники для проверок

### Сервисы
- Инкапсуляция бизнес-логики
- Транзакции для критических операций
- Обработка исключений

## ✨ Готово!

Код приведен в порядок и соответствует стандартам сеньор-программиста.
