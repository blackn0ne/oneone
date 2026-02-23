# ONEONE - Руководство по реализации

## 🎯 Быстрый старт

Этот документ содержит конкретные примеры кода для реализации ключевых функций ONEONE.

---

## 1. Настройка мультитенантности

### Установка пакета

```bash
composer require stancl/tenancy
php artisan tenancy:install
```

### Конфигурация

**config/tenancy.php:**
```php
<?php

return [
    'tenant_model' => \App\Models\Central\Tenant::class,
    'id_generator' => Stancl\Tenancy\UUIDGenerator::class,
    
    'database' => [
        'central_connection' => env('DB_CONNECTION', 'mysql'),
        'template_tenant_connection' => null,
        'prefix_base' => 'tenant_',
        'suffix_base' => '',
    ],
    
    'bootstrappers' => [
        \Stancl\Tenancy\Bootstrappers\DatabaseTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\CacheTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\FilesystemTenancyBootstrapper::class,
        \Stancl\Tenancy\Bootstrappers\QueueTenancyBootstrapper::class,
    ],
];
```

### Middleware для определения tenant

**app/Http/Middleware/IdentifyTenant.php:**
```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use App\Models\Central\Tenant;
use Stancl\Tenancy\Middleware\IdentificationMiddleware;

class IdentifyTenant extends IdentificationMiddleware
{
    public static $onFail = 'abort';

    protected function identifyByRequest(Request $request): ?Tenant
    {
        // Определение по домену
        $host = $request->getHost();
        $tenant = Tenant::whereHas('domains', function ($query) use ($host) {
            $query->where('domain', $host);
        })->first();

        // Или по поддомену
        if (!$tenant) {
            $subdomain = explode('.', $host)[0];
            $tenant = Tenant::where('id', $subdomain)->first();
        }

        return $tenant;
    }
}
```

---

## 2. Central Database Models

### Tenant Model

**app/Models/Central/Tenant.php:**
```php
<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Stancl\Tenancy\Database\Concerns\HasDatabase;
use Stancl\Tenancy\Database\Concerns\HasDomains;

class Tenant extends BaseTenant implements TenantWithDatabase
{
    use HasDatabase, HasDomains;

    protected $fillable = [
        'id',
        'name',
        'email',
        'phone',
        'status',
        'plan_id',
        'trial_ends_at',
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'trial_ends_at' => 'datetime',
        'data' => 'array',
    ];

    // Relationships
    public function subscription()
    {
        return $this->hasOne(Subscription::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function domains()
    {
        return $this->hasMany(Domain::class);
    }

    public function billings()
    {
        return $this->hasMany(Billing::class);
    }
}
```

### Subscription Model

**app/Models/Central/Subscription.php:**
```php
<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'tenant_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'cancelled_at',
        'trial_ends_at',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'trial_ends_at' => 'datetime',
    ];

    public function tenant()
    {
        return $this->belongsTo(Tenant::class);
    }

    public function plan()
    {
        return $this->belongsTo(Plan::class);
    }

    public function isActive(): bool
    {
        return $this->status === 'active' 
            && ($this->ends_at === null || $this->ends_at->isFuture());
    }
}
```

---

## 3. Tenant Database Models

### Service Model

**app/Models/Tenant/Service.php:**
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Service extends Model
{
    protected $fillable = [
        'name',
        'description',
        'duration', // в минутах
        'price',
        'category_id',
        'location_id',
        'is_active',
        'booking_mode', // service, hotel, event, online, rental, chauffeur
        'buffer_time_before', // минуты до
        'buffer_time_after', // минуты после
        'prepare_time', // время подготовки
        'max_participants', // для групповых
        'min_duration',
        'max_duration',
        'allow_custom_duration',
        'allow_recurring',
        'metadata', // JSON для специфичных данных режима
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'allow_custom_duration' => 'boolean',
        'allow_recurring' => 'boolean',
        'metadata' => 'array',
        'price' => 'decimal:2',
    ];

    // Relationships
    public function staff(): BelongsToMany
    {
        return $this->belongsToMany(Staff::class, 'service_staff');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class, 'service_extras');
    }

    public function packages(): BelongsToMany
    {
        return $this->belongsToMany(Package::class, 'package_services');
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }
}
```

### Booking Model

**app/Models/Tenant/Booking.php:**
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Booking extends Model
{
    protected $fillable = [
        'service_id',
        'staff_id',
        'customer_id',
        'location_id',
        'status', // pending, confirmed, cancelled, completed
        'booking_mode',
        'start_time',
        'end_time',
        'duration',
        'participants_count',
        'is_group',
        'is_recurring',
        'recurring_pattern', // daily, weekly, monthly, custom
        'recurring_end_date',
        'parent_booking_id', // для повторяющихся
        'price',
        'deposit',
        'total_price',
        'currency',
        'payment_status', // pending, paid, partial, refunded
        'payment_method',
        'notes',
        'metadata', // JSON для специфичных данных
    ];

    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
        'recurring_end_date' => 'datetime',
        'is_group' => 'boolean',
        'is_recurring' => 'boolean',
        'price' => 'decimal:2',
        'deposit' => 'decimal:2',
        'total_price' => 'decimal:2',
        'metadata' => 'array',
    ];

    // Relationships
    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function staff(): BelongsTo
    {
        return $this->belongsTo(Staff::class);
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function extras(): BelongsToMany
    {
        return $this->belongsToMany(Extra::class, 'booking_extras')
            ->withPivot('quantity', 'price');
    }

    public function parentBooking(): BelongsTo
    {
        return $this->belongsTo(Booking::class, 'parent_booking_id');
    }

    public function childBookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'parent_booking_id');
    }

    // Scopes
    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('start_time', '>', now());
    }
}
```

### Staff Model

**app/Models/Tenant/Staff.php:**
```php
<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Staff extends Model
{
    protected $fillable = [
        'user_id', // связь с пользователем системы
        'name',
        'email',
        'phone',
        'photo',
        'specialization',
        'is_active',
        'locations', // JSON массив ID локаций
        'working_hours', // JSON расписание
        'breaks', // JSON перерывы
        'holidays', // JSON выходные
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'locations' => 'array',
        'working_hours' => 'array',
        'breaks' => 'array',
        'holidays' => 'array',
    ];

    public function services(): BelongsToMany
    {
        return $this->belongsToMany(Service::class, 'service_staff');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function schedules(): HasMany
    {
        return $this->hasMany(Schedule::class);
    }
}
```

---

## 4. Миграции

### Central: Tenants

**database/migrations/central/0001_create_tenants_table.php:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('central')->create('tenants', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->enum('status', ['active', 'suspended', 'trial'])->default('trial');
            $table->foreignId('plan_id')->nullable()->constrained('plans');
            $table->timestamp('trial_ends_at')->nullable();
            $table->json('data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::connection('central')->dropIfExists('tenants');
    }
};
```

### Tenant: Services

**database/migrations/tenant/0001_create_services_table.php:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('duration')->default(60); // минуты
            $table->decimal('price', 10, 2)->default(0);
            $table->foreignId('category_id')->nullable()->constrained('categories');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->boolean('is_active')->default(true);
            $table->enum('booking_mode', [
                'service', 
                'hotel', 
                'event', 
                'online', 
                'rental', 
                'chauffeur'
            ])->default('service');
            $table->integer('buffer_time_before')->default(0);
            $table->integer('buffer_time_after')->default(0);
            $table->integer('prepare_time')->default(0);
            $table->integer('max_participants')->nullable();
            $table->integer('min_duration')->nullable();
            $table->integer('max_duration')->nullable();
            $table->boolean('allow_custom_duration')->default(false);
            $table->boolean('allow_recurring')->default(false);
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
```

### Tenant: Bookings

**database/migrations/tenant/0003_create_bookings_table.php:**
```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->id();
            $table->string('booking_number')->unique();
            $table->foreignId('service_id')->constrained('services');
            $table->foreignId('staff_id')->nullable()->constrained('staff');
            $table->foreignId('customer_id')->constrained('customers');
            $table->foreignId('location_id')->nullable()->constrained('locations');
            $table->enum('status', [
                'pending', 
                'confirmed', 
                'cancelled', 
                'completed', 
                'no_show'
            ])->default('pending');
            $table->enum('booking_mode', [
                'service', 
                'hotel', 
                'event', 
                'online', 
                'rental', 
                'chauffeur'
            ]);
            $table->timestamp('start_time');
            $table->timestamp('end_time');
            $table->integer('duration'); // минуты
            $table->integer('participants_count')->default(1);
            $table->boolean('is_group')->default(false);
            $table->boolean('is_recurring')->default(false);
            $table->enum('recurring_pattern', [
                'daily', 
                'weekly', 
                'monthly', 
                'custom'
            ])->nullable();
            $table->date('recurring_end_date')->nullable();
            $table->foreignId('parent_booking_id')->nullable()
                ->constrained('bookings')->nullOnDelete();
            $table->decimal('price', 10, 2)->default(0);
            $table->decimal('deposit', 10, 2)->default(0);
            $table->decimal('total_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->enum('payment_status', [
                'pending', 
                'paid', 
                'partial', 
                'refunded'
            ])->default('pending');
            $table->string('payment_method')->nullable();
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['start_time', 'end_time']);
            $table->index('status');
            $table->index('customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bookings');
    }
};
```

---

## 5. Services (Бизнес-логика)

### BookingService

**app/Services/BookingService.php:**
```php
<?php

namespace App\Services;

use App\Models\Tenant\Booking;
use App\Models\Tenant\Service;
use App\Models\Tenant\Staff;
use App\Models\Tenant\Customer;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class BookingService
{
    public function createBooking(array $data): Booking
    {
        return DB::transaction(function () use ($data) {
            $service = Service::findOrFail($data['service_id']);
            
            // Проверка доступности
            $this->checkAvailability(
                $service,
                $data['start_time'],
                $data['end_time'],
                $data['staff_id'] ?? null
            );

            // Расчет цены
            $price = $this->calculatePrice($service, $data);

            // Создание бронирования
            $booking = Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'service_id' => $data['service_id'],
                'staff_id' => $data['staff_id'] ?? null,
                'customer_id' => $data['customer_id'],
                'location_id' => $data['location_id'] ?? null,
                'status' => 'pending',
                'booking_mode' => $service->booking_mode,
                'start_time' => $data['start_time'],
                'end_time' => $data['end_time'],
                'duration' => $data['duration'] ?? $service->duration,
                'participants_count' => $data['participants_count'] ?? 1,
                'is_group' => $data['is_group'] ?? false,
                'price' => $price,
                'total_price' => $price,
                'currency' => $data['currency'] ?? 'USD',
            ]);

            // Создание повторяющихся бронирований
            if ($data['is_recurring'] ?? false) {
                $this->createRecurringBookings($booking, $data);
            }

            // Применение промокода
            if (isset($data['promo_code'])) {
                $this->applyPromoCode($booking, $data['promo_code']);
            }

            return $booking;
        });
    }

    protected function checkAvailability(
        Service $service,
        Carbon $startTime,
        Carbon $endTime,
        ?int $staffId
    ): void {
        $query = Booking::where('status', '!=', 'cancelled')
            ->where(function ($q) use ($startTime, $endTime) {
                $q->whereBetween('start_time', [$startTime, $endTime])
                  ->orWhereBetween('end_time', [$startTime, $endTime])
                  ->orWhere(function ($q2) use ($startTime, $endTime) {
                      $q2->where('start_time', '<=', $startTime)
                         ->where('end_time', '>=', $endTime);
                  });
            });

        if ($staffId) {
            $query->where('staff_id', $staffId);
        } else {
            $query->where('service_id', $service->id);
        }

        if ($query->exists()) {
            throw new \Exception('Time slot is not available');
        }
    }

    protected function calculatePrice(Service $service, array $data): float
    {
        $price = $service->price;

        // Множественные участники
        if (($data['is_group'] ?? false) && $service->max_participants) {
            $price *= ($data['participants_count'] ?? 1);
        }

        // Кастомная длительность
        if (($data['custom_duration'] ?? false) && $service->allow_custom_duration) {
            $duration = $data['duration'] ?? $service->duration;
            $price = ($price / $service->duration) * $duration;
        }

        return round($price, 2);
    }

    protected function createRecurringBookings(Booking $parent, array $data): void
    {
        $pattern = $data['recurring_pattern'];
        $endDate = Carbon::parse($data['recurring_end_date']);
        $current = Carbon::parse($parent->start_time);

        while ($current->lte($endDate)) {
            if ($current->eq($parent->start_time)) {
                $current->add($this->getRecurringInterval($pattern));
                continue;
            }

            Booking::create([
                'booking_number' => $this->generateBookingNumber(),
                'service_id' => $parent->service_id,
                'staff_id' => $parent->staff_id,
                'customer_id' => $parent->customer_id,
                'location_id' => $parent->location_id,
                'status' => 'pending',
                'booking_mode' => $parent->booking_mode,
                'start_time' => $current->copy(),
                'end_time' => $current->copy()->addMinutes($parent->duration),
                'duration' => $parent->duration,
                'participants_count' => $parent->participants_count,
                'is_group' => $parent->is_group,
                'is_recurring' => true,
                'recurring_pattern' => $pattern,
                'parent_booking_id' => $parent->id,
                'price' => $parent->price,
                'total_price' => $parent->total_price,
                'currency' => $parent->currency,
            ]);

            $current->add($this->getRecurringInterval($pattern));
        }
    }

    protected function getRecurringInterval(string $pattern): \DateInterval
    {
        return match($pattern) {
            'daily' => new \DateInterval('P1D'),
            'weekly' => new \DateInterval('P1W'),
            'monthly' => new \DateInterval('P1M'),
            default => new \DateInterval('P1W'),
        };
    }

    protected function generateBookingNumber(): string
    {
        return 'BK' . date('Ymd') . strtoupper(uniqid());
    }
}
```

---

## 6. AI Service (Интеграция с OpenAI)

**app/Services/AIService.php:**
```php
<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;
use App\Models\Tenant\Service;
use App\Models\Tenant\Booking;

class AIService
{
    public function chat(string $message, array $context = []): string
    {
        $systemPrompt = $this->buildSystemPrompt($context);

        $response = OpenAI::chat()->create([
            'model' => 'gpt-4',
            'messages' => [
                ['role' => 'system', 'content' => $systemPrompt],
                ['role' => 'user', 'content' => $message],
            ],
            'temperature' => 0.7,
        ]);

        return $response->choices[0]->message->content;
    }

    public function checkAvailability(string $serviceName, string $dateTime): array
    {
        // Логика проверки доступности через AI
        // AI может понять намерение пользователя и проверить доступность
    }

    protected function buildSystemPrompt(array $context): string
    {
        $services = Service::where('is_active', true)->get();
        $servicesList = $services->map(fn($s) => "- {$s->name}: {$s->description}")->join("\n");

        return "You are a helpful booking assistant for a service booking platform.
        
Available services:
{$servicesList}

Help customers:
1. Understand available services
2. Check availability
3. Complete bookings
4. Answer questions about services, staff, and scheduling

Be friendly, professional, and helpful.";
    }
}
```

---

Это базовые примеры. Полная реализация потребует дополнительных компонентов и тестирования.
