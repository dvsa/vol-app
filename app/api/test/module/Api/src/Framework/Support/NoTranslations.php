<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Framework\Support;

use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Laminas\I18n\Translator\TextDomain;

/**
 * Stands in for the api's TranslationLoader, which reads translations and replacements from Redis and then
 * the database.
 *
 * Translations are data, not framework: with none loaded, every message translates to its own key, which
 * keeps output deterministic and still shows where translation happens.
 */
final class NoTranslations extends TranslationLoader
{
    public function __construct()
    {
        // Deliberately not calling the parent: there is no cache or repository to read from
    }

    #[\Override]
    public function load($locale, $textDomain): TextDomain
    {
        return new TextDomain([]);
    }

    #[\Override]
    public function loadReplacements(): array
    {
        return [];
    }
}
