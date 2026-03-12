<?php

namespace App\Http\Middleware;

use App\Models\Central\Language;
use App\Models\Central\Settings;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        
        $userData = null;
        if ($user) {
            // Загружаем роли, если они еще не загружены
            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
            }
            
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role ?? 'staff',
                'is_super_admin' => $user->isSuperAdmin(),
                'roles' => $user->roles->pluck('name')->toArray(),
            ];
        }
        
        // Загружаем переводы для текущего языка
        $translations = [];
        try {
            $settings = Settings::getInstance();
            $languageCode = $settings->default_language ?? 'ru';
            
            $language = Language::where('code', $languageCode)->where('is_active', true)->first();
            if ($language) {
                $translations = $language->getTranslations();
            } else {
                // Fallback: пытаемся загрузить из файла напрямую
                $filePath = lang_path("{$languageCode}.json");
                if (file_exists($filePath)) {
                    $content = file_get_contents($filePath);
                    $translations = json_decode($content, true) ?? [];
                }
            }
        } catch (\Exception $e) {
            // Игнорируем ошибки загрузки переводов
        }
        
        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'auth' => [
                'user' => $userData,
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'translations' => $translations,
        ];
    }
}
