<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Staff;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    /**
     * Показать список ролей
     */
    public function index(Request $request): Response
    {
        $rolesPaginated = Role::on('tenant')
            ->with('permissions')
            ->latest()
            ->paginate(15);

        // Модифицируем коллекцию для добавления users_count
        $rolesPaginated->getCollection()->transform(function ($role) {
            // Подсчитываем количество сотрудников с этой ролью
            $staffCount = Staff::whereHas('roles', function ($query) use ($role) {
                $query->where('roles.id', $role->id);
            })->count();
            
            $role->permissions_count = $role->permissions->count();
            $role->users_count = $staffCount;
            
            return $role;
        });

        return Inertia::render('Roles/Index', [
            'roles' => $rolesPaginated,
        ]);
    }

    /**
     * Показать форму создания роли
     */
    public function create(Request $request): Response
    {
        $permissions = Permission::on('tenant')->get()->groupBy(function ($permission) {
            // Группируем разрешения по модулям
            $parts = explode(' ', $permission->name);
            return $parts[0]; // bookings, services, staff, etc.
        });

        return Inertia::render('Roles/Create', [
            'permissions' => $permissions->map(function ($group, $module) {
                return $group->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ])->values();
            }),
        ]);
    }

    /**
     * Сохранить новую роль
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tenant.roles,name'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:tenant.permissions,id'],
        ]);

        $role = Role::on('tenant')->create([
            'name' => $validated['name'],
            'guard_name' => 'web'
        ]);

        if (!empty($validated['permissions'])) {
            $permissions = Permission::on('tenant')->whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Роль успешно создана!');
    }

    /**
     * Показать детали роли
     */
    public function show($id): Response
    {
        $role = Role::on('tenant')->with('permissions')->findOrFail($id);
        $permissions = Permission::on('tenant')->get()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return $parts[0];
        });

        return Inertia::render('Roles/Show', [
            'role' => $role,
            'permissions' => $permissions->map(function ($group, $module) {
                return $group->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ])->values();
            }),
        ]);
    }

    /**
     * Показать форму редактирования роли
     */
    public function edit($id): Response
    {
        $role = Role::on('tenant')->with('permissions')->findOrFail($id);
        $permissions = Permission::on('tenant')->get()->groupBy(function ($permission) {
            $parts = explode(' ', $permission->name);
            return $parts[0];
        });

        return Inertia::render('Roles/Edit', [
            'role' => $role,
            'permissions' => $permissions->map(function ($group, $module) {
                return $group->map(fn($p) => [
                    'id' => $p->id,
                    'name' => $p->name,
                ])->values();
            }),
        ]);
    }

    /**
     * Обновить роль
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $role = Role::on('tenant')->findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:tenant.roles,name,' . $role->id],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['exists:tenant.permissions,id'],
        ]);

        $role->update(['name' => $validated['name']]);

        if (isset($validated['permissions'])) {
            $permissions = Permission::on('tenant')->whereIn('id', $validated['permissions'])->get();
            $role->syncPermissions($permissions);
        } else {
            $role->syncPermissions([]);
        }

        return redirect()
            ->route('roles.index')
            ->with('success', 'Роль обновлена!');
    }

    /**
     * Удалить роль
     */
    public function destroy($id): RedirectResponse
    {
        $role = Role::on('tenant')->findOrFail($id);
        
        // Нельзя удалить роль admin
        if ($role->name === 'admin') {
            return redirect()
                ->route('roles.index')
                ->with('error', 'Нельзя удалить роль администратора.');
        }

        $role->delete();

        return redirect()
            ->route('roles.index')
            ->with('success', 'Роль удалена!');
    }
}
