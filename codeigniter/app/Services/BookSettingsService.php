<?php

namespace App\Services;

use InvalidArgumentException;
use JsonException;

class BookSettingsService
{
    private const MONEY_DISPLAY_GROUP = 'money_display';
    private const MONEY_SEPARATOR_OPTIONS = [
        'comma',
        'dot',
        'space',
    ];
    private const DEFAULT_MONEY_DISPLAY = [
        'thousand_separator' => 'comma',
        'show_cents' => true,
    ];

    /**
     * Returns the settings schema available for a given book type.
     */
    public function getSchemaForBookType(array $bookType): array
    {
        if ((int) ($bookType['requires_currency'] ?? 0) !== 1) {
            return [];
        }

        return [
            self::MONEY_DISPLAY_GROUP => [
                'fields' => [
                    'thousand_separator' => [
                        'type' => 'select',
                        'options' => self::MONEY_SEPARATOR_OPTIONS,
                    ],
                    'show_cents' => [
                        'type' => 'boolean',
                    ],
                ],
            ],
        ];
    }

    /**
     * Returns the default settings object for a newly created book type.
     */
    public function buildDefaultSettingsForBookType(array $bookType): array
    {
        return $this->normalizeSettings($this->getSchemaForBookType($bookType), []);
    }

    /**
     * Normalizes settings read from the database to a stable public shape.
     */
    public function normalizeStoredSettings(array $schema, mixed $storedSettings): array
    {
        return $this->normalizeSettings($schema, $this->decodeSettings($storedSettings));
    }

    /**
     * Validates and normalizes a submitted settings payload for storage.
     */
    public function normalizeSubmittedSettings(array $schema, mixed $submittedSettings): array
    {
        if (! is_array($submittedSettings)) {
            throw new InvalidArgumentException('Settings payload must be an object.');
        }

        return $this->normalizeSettings($schema, $submittedSettings, true);
    }

    /**
     * Serializes normalized settings for database storage.
     */
    public function encodeSettingsForStorage(array $settings): ?string
    {
        if ($settings === []) {
            return null;
        }

        try {
            return json_encode($settings, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        } catch (JsonException) {
            throw new InvalidArgumentException('Unable to encode book settings.');
        }
    }

    private function normalizeSettings(array $schema, array $settings, bool $strict = false): array
    {
        foreach (array_keys($settings) as $groupKey) {
            if (! array_key_exists($groupKey, $schema)) {
                throw new InvalidArgumentException('Unknown book settings group.');
            }
        }

        $normalizedSettings = [];

        if (isset($schema[self::MONEY_DISPLAY_GROUP])) {
            $submittedMoneyDisplay = $settings[self::MONEY_DISPLAY_GROUP] ?? [];

            if (! is_array($submittedMoneyDisplay)) {
                throw new InvalidArgumentException('Money display settings must be an object.');
            }

            foreach (array_keys($submittedMoneyDisplay) as $fieldKey) {
                if (! in_array($fieldKey, ['thousand_separator', 'show_cents'], true)) {
                    throw new InvalidArgumentException('Unknown money display setting.');
                }
            }

            $normalizedSettings[self::MONEY_DISPLAY_GROUP] = $this->normalizeMoneyDisplaySettings(
                $submittedMoneyDisplay,
                $strict
            );
        }

        return $normalizedSettings;
    }

    private function decodeSettings(mixed $storedSettings): array
    {
        if (is_array($storedSettings)) {
            return $storedSettings;
        }

        if (! is_string($storedSettings) || trim($storedSettings) === '') {
            return [];
        }

        try {
            $decoded = json_decode($storedSettings, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException) {
            return [];
        }

        return is_array($decoded) ? $decoded : [];
    }

    private function normalizeMoneyDisplaySettings(mixed $settings, bool $strictSeparator = false): array
    {
        $settings = is_array($settings) ? $settings : [];
        $separator = trim((string) ($settings['thousand_separator'] ?? self::DEFAULT_MONEY_DISPLAY['thousand_separator']));

        if (! in_array($separator, self::MONEY_SEPARATOR_OPTIONS, true)) {
            if ($strictSeparator) {
                throw new InvalidArgumentException('Please choose a valid number format.');
            }

            $separator = self::DEFAULT_MONEY_DISPLAY['thousand_separator'];
        }

        return [
            'thousand_separator' => $separator,
            'show_cents' => $this->normalizeBooleanValue(
                $settings['show_cents'] ?? self::DEFAULT_MONEY_DISPLAY['show_cents'],
                self::DEFAULT_MONEY_DISPLAY['show_cents']
            ),
        ];
    }

    private function normalizeBooleanValue(mixed $value, bool $default): bool
    {
        if (is_bool($value)) {
            return $value;
        }

        if (is_int($value) || is_float($value)) {
            return (int) $value === 1;
        }

        if (is_string($value)) {
            $normalizedValue = strtolower(trim($value));

            if (in_array($normalizedValue, ['1', 'true', 'yes', 'on'], true)) {
                return true;
            }

            if (in_array($normalizedValue, ['0', 'false', 'no', 'off', ''], true)) {
                return false;
            }
        }

        return $default;
    }
}
