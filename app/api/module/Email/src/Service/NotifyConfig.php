<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Email\Service;

/**
 * Reads the GOV.UK Notify settings from the `notify` config block.
 *
 * Older local.php files set these under `email.notify`, `email.notify_test` and `email.sms`, so each setting falls back
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

    /** The DSN for admin "Send test via Notify" sends, which carries its own key, or null to hide the button. */
    public static function testDsn(array $config): ?string
    {
        return self::first($config['notify']['email']['test_dsn'] ?? null, $config['email']['notify_test']['dsn'] ?? null);
    }

    /** The id of the Notify SMS template. */
    public static function smsTemplateId(array $config): ?string
    {
        return self::first($config['notify']['sms']['template_id'] ?? null, $config['email']['sms']['template_id'] ?? null);
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
