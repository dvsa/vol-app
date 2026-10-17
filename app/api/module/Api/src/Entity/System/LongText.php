<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Entity\System;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'long_text')]
#[ORM\Entity]
#[ORM\UniqueConstraint(name: 'uk_long_text_reference_key_locale', columns: ['reference_key', 'locale'])]
class LongText extends AbstractLongText
{
    private const REFERENCE_KEY_PATTERN = '/^[a-z0-9]+(-[a-z0-9]+)*$/';

    public const LOCALES = ['en_GB', 'cy_GB', 'en_NI', 'cy_NI'];

    public const DEFAULT_LOCALE = 'en_GB';

    public static function create(
        string $referenceKey,
        string $locale,
        string $pageName,
        ?string $description,
        array $content,
    ): self {
        self::assertValidReferenceKey($referenceKey);
        self::assertSupportedLocale($locale);

        $instance = new self();
        $instance->referenceKey = $referenceKey;
        $instance->locale = $locale;
        $instance->pageName = $pageName;
        $instance->description = $description;
        $instance->content = $content;

        return $instance;
    }

    private static function assertSupportedLocale(string $locale): void
    {
        if (!in_array($locale, self::LOCALES, true)) {
            throw new \InvalidArgumentException(sprintf(
                'Locale "%s" is not one of %s',
                $locale,
                implode(', ', self::LOCALES),
            ));
        }
    }

    private static function assertValidReferenceKey(string $referenceKey): void
    {
        if (preg_match(self::REFERENCE_KEY_PATTERN, $referenceKey) !== 1) {
            throw new \InvalidArgumentException(sprintf(
                'Reference key "%s" must be lowercase words separated by single hyphens, '
                . 'so that application code can address it',
                $referenceKey,
            ));
        }
    }
}
