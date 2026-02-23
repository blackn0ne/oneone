<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Http\Requests\Central\StoreLanguageRequest;
use App\Http\Requests\Central\UpdateLanguageRequest;
use App\Models\Central\Language;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Контроллер для управления языками
 */
class LanguageController extends Controller
{
    /**
     * Отобразить список всех языков
     *
     * @return Response
     */
    public function index(): Response
    {
        $languages = Language::orderBy('sort_order')
            ->orderBy('name')
            ->get();

        return Inertia::render('Central/Languages/Index', [
            'languages' => $languages,
        ]);
    }

    /**
     * Показать форму создания нового языка
     *
     * @return Response
     */
    public function create(): Response
    {
        return Inertia::render('Central/Languages/Create');
    }

    /**
     * Сохранить новый язык
     *
     * @param StoreLanguageRequest $request
     * @return RedirectResponse
     */
    public function store(StoreLanguageRequest $request): RedirectResponse
    {
        $language = Language::create($request->validated());

        // Создаем пустой JSON файл для переводов
        $language->saveTranslations([]);

        return redirect()
            ->route('central.languages.index')
            ->with('success', 'Язык успешно создан!');
    }

    /**
     * Отобразить детальную информацию о языке
     *
     * @param Language $language
     * @return Response
     */
    public function show(Language $language): Response
    {
        $translations = $language->getTranslations();

        return Inertia::render('Central/Languages/Show', [
            'language' => $language,
            'translations' => $translations,
        ]);
    }

    /**
     * Показать форму редактирования языка
     *
     * @param Language $language
     * @return Response
     */
    public function edit(Language $language): Response
    {
        return Inertia::render('Central/Languages/Edit', [
            'language' => $language,
        ]);
    }

    /**
     * Обновить язык
     *
     * @param UpdateLanguageRequest $request
     * @param Language $language
     * @return RedirectResponse
     */
    public function update(UpdateLanguageRequest $request, Language $language): RedirectResponse
    {
        $language->update($request->validated());

        return redirect()
            ->route('central.languages.show', $language)
            ->with('success', 'Язык обновлен!');
    }

    /**
     * Удалить язык
     *
     * @param Language $language
     * @return RedirectResponse
     */
    public function destroy(Language $language): RedirectResponse
    {
        // Удаляем файл переводов, если существует
        if ($language->translationFileExists()) {
            @unlink($language->getTranslationFilePath());
        }

        $language->delete();

        return redirect()
            ->route('central.languages.index')
            ->with('success', 'Язык удален!');
    }

    /**
     * Обновить переводы для языка
     *
     * @param \Illuminate\Http\Request $request
     * @param Language $language
     * @return RedirectResponse
     */
    public function updateTranslations(\Illuminate\Http\Request $request, Language $language): RedirectResponse
    {
        $validated = $request->validate([
            'translations' => ['required', 'array'],
        ]);

        $language->saveTranslations($validated['translations']);

        return back()->with('success', 'Переводы успешно обновлены!');
    }
}
