<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Utils\Translation;

use Laminas\I18n\Translator\TranslatorInterface as I18nTranslatorInterface;
use Laminas\Validator\Translator\TranslatorInterface as ValidatorTranslatorInterface;

/**
 * Wraps an i18n translator and applies token replacements to every translated
 * message. Composition over inheritance — no longer extends the discontinued
 * `Laminas\Mvc\I18n\Translator`. The validator translator interface is
 * implemented directly so form/validator components continue to accept it.
 */
class TranslatorDelegator implements I18nTranslatorInterface, ValidatorTranslatorInterface
{
    public function __construct(
        protected I18nTranslatorInterface $translator,
        protected Replacements $replacements,
    ) {
    }

    /**
     * Proxy any non-interface translator methods (setCache, setLocale,
     * enableEventManager, getEventManager, etc.) to the wrapped translator.
     *
     * @param string $method
     * @param array<int, mixed> $args
     */
    public function __call($method, array $args): mixed
    {
        return call_user_func_array([$this->translator, $method], $args);
    }

    public function getTranslator(): I18nTranslatorInterface
    {
        return $this->translator;
    }

    /**
     * Translate a message and apply token replacements.
     *
     * @param string|null $message
     * @param string $textDomain
     * @param string|null $locale
     */
    #[\Override]
    public function translate($message, $textDomain = 'default', $locale = null): string
    {
        if (empty($message)) {
            return '';
        }

        return $this->replacements->apply($this->translator->translate($message, $textDomain, $locale));
    }

    /**
     * Pluralised translation, with token replacements applied to the result.
     *
     * @param string $singular
     * @param string $plural
     * @param int $number
     * @param string $textDomain
     * @param string|null $locale
     */
    #[\Override]
    public function translatePlural(
        $singular,
        $plural,
        $number,
        $textDomain = 'default',
        $locale = null,
    ): string {
        return $this->replacements->apply(
            $this->translator->translatePlural($singular, $plural, $number, $textDomain, $locale),
        );
    }
}
