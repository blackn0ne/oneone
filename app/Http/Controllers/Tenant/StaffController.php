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
        // Убеждаемся, что роли созданы
        $this->ensureRolesExist();
        
        $staff = Staff::with('roles')->withCount('bookings')
            ->latest()
            ->paginate(15);
        
        $roles = \Spatie\Permission\Models\Role::on('tenant')
            ->whereIn('name', ['Мастер', 'Менеджер', 'Админ'])
            ->orderByRaw("FIELD(name, 'Мастер', 'Менеджер', 'Админ')")
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            });

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
            'roles' => $roles,
        ]);
    }

    /**
     * Показать форму создания сотрудника
     */
    public function create(Request $request): Response
    {
        // Убеждаемся, что роли созданы
        $this->ensureRolesExist();
        
        $roles = \Spatie\Permission\Models\Role::on('tenant')
            ->whereIn('name', ['Мастер', 'Менеджер', 'Админ'])
            ->orderByRaw("FIELD(name, 'Мастер', 'Менеджер', 'Админ')")
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            });

        return Inertia::render('Staff/Create', [
            'roles' => $roles,
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
            'view business', 'create business', 'edit business', 'delete business',
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
     * Убедиться, что роли существуют в tenant БД
     */
    protected function ensureRolesExist(): void
    {
        // Сначала убеждаемся, что разрешения существуют
        $this->ensurePermissionsExist();
        
        // Запускаем сидер для создания ролей
        $seeder = new \Database\Seeders\TenantRolePermissionSeeder();
        $seeder->run();
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
            'breaks' => ['nullable', 'array'],
            'holidays' => ['nullable', 'array'],
            'role_id' => ['nullable', 'exists:tenant.roles,id'],
            'password' => ['nullable', 'string', 'min:8'],
        ]);

        $roleId = $validated['role_id'] ?? null;
        unset($validated['role_id']);
        
        $password = $validated['password'] ?? null;
        unset($validated['password']);

        // Создаем User, если указан phone
        $userId = null;
        if (!empty($validated['phone'])) {
            $user = \App\Models\User::firstOrCreate(
                ['phone' => $validated['phone']],
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'password' => $password ? \Hash::make($password) : \Hash::make($validated['phone']), // По умолчанию пароль = телефон
                    'role' => 'staff',
                ]
            );
            
            // Обновляем пароль, если он был указан
            if ($password && $user->wasRecentlyCreated === false) {
                $user->password = \Hash::make($password);
                $user->save();
            }
            
            $userId = $user->id;
        }

        $validated['user_id'] = $userId;
        $staff = Staff::create($validated);

        // Назначаем роль сотруднику
        if ($roleId) {
            // Преобразуем строку в число, если необходимо
            $roleId = is_string($roleId) ? (int) $roleId : $roleId;
            
            $role = \Spatie\Permission\Models\Role::on('tenant')
                ->where('guard_name', 'web')
                ->find($roleId);
            if ($role) {
                // Используем строковое имя роли для избежания конфликта guard
                $staff->syncRoles([$role->name]);
            }
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
        // Убеждаемся, что роли созданы
        $this->ensureRolesExist();
        
        $staff = Staff::with('roles')->findOrFail($id);
        
        $roles = \Spatie\Permission\Models\Role::on('tenant')
            ->whereIn('name', ['Мастер', 'Менеджер', 'Админ'])
            ->orderByRaw("FIELD(name, 'Мастер', 'Менеджер', 'Админ')")
            ->get()
            ->map(function ($role) {
                return [
                    'id' => $role->id,
                    'name' => $role->name,
                ];
            });

        return Inertia::render('Staff/Edit', [
            'staff' => $staff,
            'roles' => $roles,
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
            'breaks' => ['nullable', 'array'],
            'holidays' => ['nullable', 'array'],
            'role_id' => ['nullable', 'exists:tenant.roles,id'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $roleId = $validated['role_id'] ?? null;
        unset($validated['role_id']);
        
        $password = $validated['password'] ?? null;
        unset($validated['password'], $validated['password_confirmation']);

        $staff->update($validated);

        // Обновляем User, если он существует
        if ($staff->user_id) {
            $user = \App\Models\User::find($staff->user_id);
            if ($user) {
                // Обновляем телефон, если он изменился
                if (isset($validated['phone']) && $user->phone !== $validated['phone']) {
                    $user->phone = $validated['phone'];
                }
                
                // Обновляем имя, если оно изменилось
                if (isset($validated['name']) && $user->name !== $validated['name']) {
                    $user->name = $validated['name'];
                }
                
                // Обновляем пароль, если он указан
                if ($password) {
                    $user->password = \Hash::make($password);
                }
                
                $user->save();
            }
        } elseif (!empty($validated['phone'])) {
            // Создаем User, если phone указан, но user_id отсутствует
            $user = \App\Models\User::firstOrCreate(
                ['phone' => $validated['phone']],
                [
                    'name' => $validated['name'],
                    'email' => $validated['email'] ?? null,
                    'password' => $password ? \Hash::make($password) : \Hash::make($validated['phone']),
                    'role' => 'staff',
                ]
            );
            
            $staff->user_id = $user->id;
            $staff->save();
        }

        // Обновляем роль сотрудника, если она передана
        if ($roleId !== null) {
            // Преобразуем строку в число, если необходимо
            $roleId = is_string($roleId) ? (int) $roleId : $roleId;
            
            if ($roleId) {
                $role = \Spatie\Permission\Models\Role::on('tenant')
                    ->where('guard_name', 'web')
                    ->find($roleId);
                if ($role) {
                    // Используем строковое имя роли для избежания конфликта guard
                    $staff->syncRoles([$role->name]);
                }
            } else {
                // Удаляем все роли, если role_id пустой
                $staff->syncRoles([]);
            }
        }

        return redirect()
            ->route('staff.index')
            ->with('success', 'Сотрудник обновлен!');
    }

    /**
     * Удалить сотрудника
     *
     * @param int $id
     * @return RedirectResponse
     */
    public function destroy($id): RedirectResponse
    {
        $staff = Staff::findOrFail($id);
        $staff->delete();

        return redirect()
            ->route('staff.index')
            ->with('success', 'Сотрудник удален!');
    }
}
