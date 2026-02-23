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

        return Inertia::render('Staff/Index', [
            'staff' => $staff,
        ]);
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
            'email' => ['nullable', 'email', 'unique:staff,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'working_hours' => ['nullable', 'array'],
            'breaks' => ['nullable', 'array'],
            'holidays' => ['nullable', 'array'],
        ]);

        $staff = Staff::create($validated);

        return redirect()
            ->route('staff.show', $staff)
            ->with('success', 'Сотрудник успешно создан!');
    }

    public function show(Staff $staff): Response
    {
        $staff->load(['services', 'bookings.service']);

        return Inertia::render('Staff/Show', [
            'staff' => $staff,
        ]);
    }

    /**
     * Обновить сотрудника
     *
     * @param Request $request
     * @param Staff $staff
     * @return RedirectResponse
     */
    public function update(Request $request, Staff $staff): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:staff,email,' . $staff->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'specialization' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
            'working_hours' => ['nullable', 'array'],
            'breaks' => ['nullable', 'array'],
            'holidays' => ['nullable', 'array'],
        ]);

        $staff->update($validated);

        return redirect()
            ->route('staff.show', $staff)
            ->with('success', 'Сотрудник обновлен!');
    }
}
