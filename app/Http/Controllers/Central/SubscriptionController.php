<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Subscription;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления подписками tenants
 */
class SubscriptionController extends Controller
{
    /**
     * Отобразить список всех подписок
     *
     * @return Response
     */
    public function index(): Response
    {
        $subscriptions = Subscription::with(['tenant', 'plan'])
            ->latest()
            ->paginate(15);

        return Inertia::render('Central/Subscriptions/Index', [
            'subscriptions' => $subscriptions,
        ]);
    }

    /**
     * Отобразить детальную информацию о подписке
     *
     * @param Subscription $subscription
     * @return Response
     */
    public function show(Subscription $subscription): Response
    {
        $subscription->load(['tenant', 'plan']);

        return Inertia::render('Central/Subscriptions/Show', [
            'subscription' => $subscription,
        ]);
    }
}
