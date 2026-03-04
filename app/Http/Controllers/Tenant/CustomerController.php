<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления клиентами
 */
class CustomerController extends Controller
{
    public function index(Request $request): Response
    {
        $customers = Customer::withCount('bookings')
            ->latest()
            ->paginate(15);

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Показать форму создания клиента
     */
    public function create(Request $request): Response
    {
        return Inertia::render('Customers/Create');
    }

    /**
     * Сохранить нового клиента
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:customers,email'],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer = Customer::create($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Клиент успешно создан!');
    }

    public function show($id): Response
    {
        $customer = Customer::with(['bookings.service', 'bookings.staff'])->findOrFail($id);

        return Inertia::render('Customers/Show', [
            'customer' => $customer,
        ]);
    }

    /**
     * Обновить клиента
     *
     * @param Request $request
     * @param int $id
     * @return RedirectResponse
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'unique:customers,email,' . $customer->id],
            'phone' => ['nullable', 'string', 'max:20'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        $customer->update($validated);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Клиент обновлен!');
    }
}
