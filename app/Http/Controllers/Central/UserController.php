<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreUserRequest;
use App\Http\Requests\Central\UpdateUserRequest;
use App\Models\User;
use App\Models\Central\Tenant;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Inertia\Inertia;
use Inertia\Response;
use Stancl\Tenancy\Facades\Tenancy;

/**
 * Контроллер для управления пользователями системы
 */
class UserController extends Controller
{
    /**
     * Отобразить список всех пользователей
     *
     * @return Response
     */
    public function index(): Response
    {
        $users = User::with('roles')
            ->latest()
            ->paginate(15);

        // Получаем информацию о связях с tenant для каждого пользователя
        $users->getCollection()->transform(function ($user) {
            $user->tenant_connections = $this->getUserTenantConnections($user);
            return $user;
        });

        return Inertia::render('Central/Users/Index', [
            'users' => $users,
        ]);
    }

    /**
     * Показать форму создания нового пользователя
     *
     * @return Response
     */
    public function create(): Response
    {
        $tenants = Tenant::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Central/Users/Create', [
            'tenants' => $tenants,
        ]);
    }

    /**
     * Сохранить нового пользователя
     *
     * @param StoreUserRequest $request
     * @return RedirectResponse
     */
    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);

        $user = User::create($data);

        // Назначаем роли, если указаны
        if ($request->has('roles') && is_array($request->roles)) {
            $user->syncRoles($request->roles);
        }

        return redirect()
            ->route('central.users.show', $user)
            ->with('success', 'Пользователь успешно создан!');
    }

    /**
     * Отобразить детальную информацию о пользователе
     *
     * @param User $user
     * @return Response
     */
    public function show(User $user): Response
    {
        $user->load('roles');
        $user->tenant_connections = $this->getUserTenantConnections($user);

        $tenants = Tenant::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Central/Users/Show', [
            'user' => $user,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Показать форму редактирования пользователя
     *
     * @param User $user
     * @return Response
     */
    public function edit(User $user): Response
    {
        $user->load('roles');
        $user->tenant_connections = $this->getUserTenantConnections($user);

        $tenants = Tenant::where('status', 'active')
            ->orderBy('name')
            ->get(['id', 'name', 'email']);

        return Inertia::render('Central/Users/Edit', [
            'user' => $user,
            'tenants' => $tenants,
        ]);
    }

    /**
     * Обновить пользователя
     *
     * @param UpdateUserRequest $request
     * @param User $user
     * @return RedirectResponse
     */
    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        // Хешируем пароль только если он был указан
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);

        // Обновляем роли, если указаны
        if ($request->has('roles') && is_array($request->roles)) {
            $user->syncRoles($request->roles);
        }

        return redirect()
            ->route('central.users.show', $user)
            ->with('success', 'Пользователь обновлен!');
    }

    /**
     * Удалить пользователя
     *
     * @param User $user
     * @return RedirectResponse
     */
    public function destroy(User $user): RedirectResponse
    {
        // Нельзя удалить супер-админа
        if ($user->isSuperAdmin()) {
            return redirect()
                ->route('central.users.index')
                ->with('error', 'Нельзя удалить супер-администратора!');
        }

        $user->delete();

        return redirect()
            ->route('central.users.index')
            ->with('success', 'Пользователь удален!');
    }

    /**
     * Получить связи пользователя с tenant через Staff
     *
     * @param User $user
     * @return array
     */
    protected function getUserTenantConnections(User $user): array
    {
        $connections = [];

        // Проходим по всем активным tenant
        $tenants = Tenant::where('status', 'active')->get();

        foreach ($tenants as $tenant) {
            try {
                // Переключаемся на базу данных tenant
                tenancy()->initialize($tenant);

                // Ищем Staff с этим user_id
                // После initialize() все модели автоматически используют tenant подключение
                $staff = \App\Models\Tenant\Staff::where('user_id', $user->id)->first();

                if ($staff) {
                    $connections[] = [
                        'tenant_id' => $tenant->id,
                        'tenant_name' => $tenant->name,
                        'staff_id' => $staff->id,
                        'staff_name' => $staff->name,
                        'staff_email' => $staff->email,
                        'staff_phone' => $staff->phone,
                        'is_active' => $staff->is_active,
                    ];
                }
            } catch (\Exception $e) {
                // Игнорируем ошибки доступа к tenant базе
                continue;
            } finally {
                // Возвращаемся к центральной базе
                tenancy()->end();
            }
        }

        return $connections;
    }
}
