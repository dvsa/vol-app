<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Service;

/**
 * Reads the GOV.UK Notify settings from the `notify` config block.
 *
 * Older local.php files set these under `email.notify` and `email.sms`, so each setting falls back
 * to its old place when the new one is blank. Blank means null, empty or whitespace, the "null"
 * sentinel (any case), or a `%placeholder%` that was never resolved.
 */
final class NotifyConfig
{
    /** The Notify API key, used when the mail DSN doesn't carry one. */
    public static function apiKey(array $config): ?string
    {
        return self::setOrNull($config['notify']['api_key'] ?? null);
    }

    /** The forward proxy for Notify API calls, or null for a direct connection. */
    public static function proxy(array $config): ?string
    {
        return self::first($config['notify']['proxy'] ?? null, $config['email']['notify']['proxy'] ?? null);
    }

    /**
     * The passthrough email template id for each locale.
     *
     * @return array<string, string> locale => Notify template id
     */
    public static function passthroughTemplates(array $config): array
    {
        $new = $config['notify']['email']['passthrough_templates'] ?? [];
        $old = $config['email']['notify']['passthrough_templates'] ?? [];
        $new = is_array($new) ? $new : [];
        $old = is_array($old) ? $old : [];

        $templates = [];
        foreach (array_keys($new + $old) as $locale) {
            $id = self::first($new[$locale] ?? null, $old[$locale] ?? null);
            if ($id !== null) {
                $templates[(string) $locale] = $id;
            }
        }

        return $templates;
    }

    /** A replacement for the local email preview chrome (tests and local tinkering only). */
    public static function devChromeTemplate(array $config): ?string
    {
        $new = $config['notify']['email']['dev_chrome_template'] ?? null;
        $old = $config['email']['notify']['dev_chrome_template'] ?? null;

        return is_string($new) ? $new : (is_string($old) ? $old : null);
    }

    /**
     * The SMS settings, each still raw (SmsConfigFactory decides what a value means).
     *
     * @return array{template_id: mixed, send_all_sms_to: mixed, is_production: bool}
     */
    public static function sms(array $config): array
    {
        $new = $config['notify']['sms'] ?? [];
        $old = $config['email']['sms'] ?? [];
        $new = is_array($new) ? $new : [];
        $old = is_array($old) ? $old : [];

        return [
            'template_id' => self::firstRaw($new['template_id'] ?? null, $old['template_id'] ?? null),
            'send_all_sms_to' => self::firstRaw($new['send_all_sms_to'] ?? null, $old['send_all_sms_to'] ?? null),
            'is_production' => (bool) ($new['is_production'] ?? $old['is_production'] ?? false),
        ];
    }

    private static function first(mixed ...$values): ?string
    {
        foreach ($values as $value) {
            $value = self::setOrNull($value);
            if ($value !== null) {
                return $value;
            }
        }

        return null;
    }

    /** The first value that isn't blank, as it was given, so a typo still reaches its validation. */
    private static function firstRaw(mixed ...$values): mixed
    {
        foreach ($values as $value) {
            if (self::setOrNull($value) !== null) {
                return $value;
            }
        }

        return null;
    }

    private static function setOrNull(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        if ($value === '' || strcasecmp($value, 'null') === 0 || preg_match('/^%[^%]+%$/', $value)) {
            return null;
        }

        return $value;
    }
}
