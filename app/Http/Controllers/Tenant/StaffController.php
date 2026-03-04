<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления сотрудниками
 */
class StaffController extends Controller
{
    public function index(Request $request): Response
    {
        $staff = Staff::withCount('bookings')
            ->latest()
            ->paginate(15);

        // Данные для формы создания
        $this->ensurePermissionsExist();
        $allPermissions = \Spatie\Permission\Models\Permission::on('tenant')
            ->orderBy('name')
            ->get();

        $permissions = $allPermissions
            ->groupBy(function ($permission) {
                $parts = explode(' ', $permission->name);
                return $parts[0];
            })
            ->map(function ($group, $module) {
                return $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                })->values();
            });

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Показать форму создания сотрудника
     */
    public function create(Request $request): Response
    {
        // Убеждаемся, что разрешения созданы в tenant БД
        $this->ensurePermissionsExist();
        
        $allPermissions = \Spatie\Permission\Models\Permission::on('tenant')
            ->orderBy('name')
            ->get();

        $permissions = $allPermissions
            ->groupBy(function ($permission) {
                // Группируем разрешения по модулям (первое слово)
                $parts = explode(' ', $permission->name);
                return $parts[0]; // bookings, services, staff, etc.
            })
            ->map(function ($group, $module) {
                return $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                })->values();
            });

        return Inertia::render('Staff/Create', [
            'permissions' => $permissions,
        ]);
    }

    /**
     * Убедиться, что разрешения существуют в tenant БД
     */
    protected function ensurePermissionsExist(): void
    {
        $permissionList = [
            'view bookings', 'create bookings', 'edit bookings', 'delete bookings', 'cancel bookings',
            'view services', 'create services', 'edit services', 'delete services',
            'view staff', 'create staff', 'edit staff', 'delete staff',
            'view customers', 'create customers', 'edit customers', 'delete customers',
            'view locations', 'create locations', 'edit locations', 'delete locations',
            'view settings', 'edit settings',
            'view reports',
            'manage all',
        ];

        foreach ($permissionList as $permissionName) {
            \Spatie\Permission\Models\Permission::on('tenant')->firstOrCreate(
                ['name' => $permissionName, 'guard_name' => 'web']
            );
        }
    }

    /**
     * Сохранить нового сотрудника
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:tenant.staff,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'working_hours' => ['nullable', 'array'],
            'breaks' => ['nullable', 'array'],
            'holidays' => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:tenant.permissions,id'],
        ]);

        $permissions = $validated['permissions'] ?? [];
        unset($validated['permissions']);

        $staff = Staff::create($validated);

        // Назначаем разрешения сотруднику напрямую
        if (!empty($permissions)) {
            $permissionModels = \Spatie\Permission\Models\Permission::on('tenant')->whereIn('id', $permissions)->get();
            $staff->syncPermissions($permissionModels);
        }

        return redirect()
            ->route('staff.index')
            ->with('success', 'Сотрудник успешно создан!');
    }

    public function show($id): Response
    {
        $staff = Staff::with(['services', 'bookings.service'])->findOrFail($id);

        return Inertia::render('Staff/Show', [
            'staff' => $staff,
        ]);
    }

    /**
     * Показать форму редактирования сотрудника
     */
    public function edit($id): Response
    {
        // Убеждаемся, что разрешения созданы в tenant БД
        $this->ensurePermissionsExist();
        
        $staff = Staff::with('permissions')->findOrFail($id);
        $permissions = \Spatie\Permission\Models\Permission::on('tenant')
            ->orderBy('name')
            ->get()
            ->groupBy(function ($permission) {
                // Группируем разрешения по модулям (первое слово)
                $parts = explode(' ', $permission->name);
                return $parts[0]; // bookings, services, staff, etc.
            })
            ->map(function ($group, $module) {
                return $group->map(function ($permission) {
                    return [
                        'id' => $permission->id,
                        'name' => $permission->name,
                    ];
                })->values();
            });

        return Inertia::render('Staff/Edit', [
            'staff' => $staff,
            'permissions' => $permissions,
        ]);
    }

    /**
     * Обновить сотрудника
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $staff = Staff::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:tenant.staff,email,' . $staff->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'working_hours' => ['nullable', 'array'],
            'breaks' => ['nullable', 'array'],
            'holidays' => ['nullable', 'array'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:tenant.permissions,id'],
        ]);

        $permissions = $validated['permissions'] ?? null;
        unset($validated['permissions']);

        $staff->update($validated);

        // Обновляем разрешения сотрудника, если они переданы
        if ($permissions !== null) {
            $permissionModels = \Spatie\Permission\Models\Permission::on('tenant')->whereIn('id', $permissions)->get();
            $staff->syncPermissions($permissionModels);
        }

        return redirect()
            ->route('staff.index')
            ->with('success', 'Сотрудник обновлен!');
    }
}
