<?php
// app/Services/ExportService.php
namespace App\Services;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ExportService
{
    protected $cachePrefix = 'translation_';
    protected $cacheTtl = 3600; // 1 hour in seconds (you had 3, which seems too short)

    public function exportForLocale(string $locale): array
    {
        $cacheKey = $this->cachePrefix . $locale;

        return Cache::remember($cacheKey, $this->cacheTtl, function () use ($locale) {
            return $this->getFormattedTranslations($locale);
        });
    }

    public function exportAll(): array
    {
        $locales = $this->getAvailableLocales();
        $results = [];

        foreach ($locales as $locale) {
            $results[$locale] = $this->exportForLocale($locale);
        }

        return $results;
    }

    protected function getAvailableLocales(): array
    {
        return Cache::remember('available_locales', $this->cacheTtl, function () {
            return Language::pluck('code')->toArray();
        });
    }

    protected function getFormattedTranslations(string $locale): array
    {
        $languageId = Cache::remember("language_id_$locale", $this->cacheTtl, function () use ($locale) {
            return Language::where('code', $locale)->value('id');
        });

        if (!$languageId) {
            return [];
        }

        $result = [];
        // Use cursor() for lower memory usage
        $translations = Translation::where('language_id', $languageId)
            ->select('key', 'content')
            ->orderBy('key')
            ->cursor();

        foreach ($translations as $translation) {
            $this->setNestedArrayValue($result, $translation->key, $translation->content);
        }

        return $result;
    }

    /**
     * Optimized version of setNestedArrayValue that minimizes memory operations
     */
    protected function setNestedArrayValue(array &$array, string $path, $value): void
    {
        // Use string explosion only once instead of inside the loop
        $keys = explode('.', $path);
        $lastKey = array_pop($keys);

        // Navigate to the correct position
        $current = &$array;
        foreach ($keys as $key) {
            if (!isset($current[$key])) {
                $current[$key] = [];
            }
            $current = &$current[$key];
        }

        // Set the value directly at the final position
        $current[$lastKey] = $value;
    }

    public function invalidateCache(string $locale): void
    {
        Cache::forget($this->cachePrefix . $locale);
    }

    public function invalidateAllCaches(): void
    {
        Cache::forget('available_locales');

        $locales = Language::pluck('code')->toArray();
        foreach ($locales as $locale) {
            $this->invalidateCache($locale);
            Cache::forget("language_id_$locale");
        }
    }
}
