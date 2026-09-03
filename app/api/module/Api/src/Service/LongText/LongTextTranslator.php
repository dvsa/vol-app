<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Service\LongText;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\LongText as LongTextRepo;
use Dvsa\Olcs\Api\Entity\System\LongText;
use Dvsa\Olcs\Api\Service\EditorJs\LongTextConverterService;
use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;
use Olcs\Logging\Log\Logger;

/**
 * Serves managed Long Text in place of a translation where one exists.
 */
final class LongTextTranslator implements TranslatorInterface
{
    private const MANAGED_PREFIX = 'markup-';

    /** @var array<string, string|null> */
    private array $resolved = [];

    public function __construct(
        private readonly TranslatorInterface $translator,
        private readonly LongTextRepo $repository,
        private readonly LongTextConverterService $converter,
    ) {
    }

    public static function referenceKeyFor(string $translationKey): string
    {
        $key = substr($translationKey, strlen(self::MANAGED_PREFIX));

        return strtolower(str_replace('_', '-', $key));
    }

    #[\Override]
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        if (!str_starts_with((string) $message, self::MANAGED_PREFIX)) {
            return $this->translator->translate($message, $textDomain, $locale);
        }

        $managed = $this->managedContent((string) $message, $locale);

        return $managed ?? $this->translator->translate($message, $textDomain, $locale);
    }

    #[\Override]
    public function translatePlural(
        $singular,
        $plural,
        $number,
        $textDomain = 'default',
        $locale = null,
    ) {
        return $this->translator->translatePlural($singular, $plural, $number, $textDomain, $locale);
    }

    private function managedContent(string $message, ?string $locale): ?string
    {
        $resolvedLocale = $locale ?? $this->currentLocale();
        $cacheKey = $message . '|' . $resolvedLocale;

        if (array_key_exists($cacheKey, $this->resolved)) {
            return $this->resolved[$cacheKey];
        }

        return $this->resolved[$cacheKey] = $this->fetch($message, $resolvedLocale);
    }

    private function fetch(string $message, string $locale): ?string
    {
        try {
            $longText = $this->repository->fetchByReferenceKey(self::referenceKeyFor($message), $locale);

            return $this->converter->convertJsonToHtml(
                json_encode($longText->getContent(), JSON_THROW_ON_ERROR),
            );
        } catch (NotFoundException) {
            return null;
        } catch (\Throwable $e) {
            Logger::err(sprintf(
                'Long Text "%s" could not be served for locale %s: %s',
                $message,
                $locale,
                $e->getMessage(),
            ), ['exception' => $e]);

            throw $e;
        }
    }

    private function currentLocale(): string
    {
        return $this->translator instanceof Translator
            ? $this->translator->getLocale()
            : LongText::DEFAULT_LOCALE;
    }
}
