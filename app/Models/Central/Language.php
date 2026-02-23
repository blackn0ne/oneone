<?php

namespace App\Models\Central;

use Illuminate\Database\Eloquent\Model;

/**
 * Модель языка
 *
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $is_active
 * @property int $sort_order
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class Language extends Model
{
    protected $connection = 'central';

    protected $fillable = [
        'name',
        'code',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    /**
     * Получить путь к JSON файлу переводов
     */
    public function getTranslationFilePath(): string
    {
        return lang_path("{$this->code}.json");
    }

    /**
     * Проверить существование файла переводов
     */
    public function translationFileExists(): bool
    {
        return file_exists($this->getTranslationFilePath());
    }

    /**
     * Получить содержимое файла переводов
     */
    public function getTranslations(): array
    {
        $filePath = $this->getTranslationFilePath();
        
        if (!file_exists($filePath)) {
            return [];
        }

        $content = file_get_contents($filePath);
        return json_decode($content, true) ?? [];
    }

    /**
     * Сохранить переводы в файл
     */
    public function saveTranslations(array $translations): bool
    {
        $filePath = $this->getTranslationFilePath();
        $directory = dirname($filePath);

        if (!is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        return file_put_contents($filePath, json_encode($translations, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) !== false;
    }
}
