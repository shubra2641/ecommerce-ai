<?php

namespace App\Models\Traits;

trait HasTranslations
{
    /**
     * Initialize trait: ensure translations cast exists and default translatable fields.
     */
    public function initializeHasTranslations(): void
    {
        // Ensure translations column is cast to array
        $this->casts = array_merge($this->casts ?? [], ['translations' => 'array']);

        // Provide a default translatable array if not defined in model
        if (!property_exists($this, 'translatable')) {
            $this->translatable = ['title'];
        }
    }

    /**
     * Override getAttribute to return translated value when available.
     */
    public function getAttribute($key)
    {
        $default = parent::getAttribute($key);
        $locale = app()->getLocale();

        if (in_array($key, $this->translatable ?? [])) {
            $translations = $this->translations ?? [];
            return $translations[$locale][$key] ?? $default;
        }

        return $default;
    }
}
