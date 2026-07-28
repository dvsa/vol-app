<?php

namespace Common\Service\Helper;

use Laminas\I18n\Translator\TranslatorInterface;

class TranslationHelperService
{
    public function __construct(protected TranslatorInterface $translator)
    {
    }

    /**
     * Allows you to replace variables after the string is translated
     *
     * Translation strings legitimately carry markup and are echoed raw at every call site, so the
     * substituted values have to be escaped here — otherwise anything reaching $arguments lands in
     * HTML unescaped. The values are scalars (counts, names, dates, route-generated URLs), never
     * markup, so escaping them is safe across all call sites.
     *
     * Note this does not address the other half of the problem: the translation string itself is
     * DB-backed and editable from the internal admin app, so it remains an injection path into the
     * public selfserve app. Purifying the string needs a policy decision on who may edit
     * translations and is deliberately not attempted here.
     *
     * @param string $translationKey
     * @param string $translateToWelsh 'Y' or 'N', Force the translation into welsh
     * @return string
     */
    public function translateReplace($translationKey, array $arguments, $translateToWelsh = 'N')
    {
        $escaped = array_map(
            static fn($argument) => htmlspecialchars((string)$argument, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'),
            $arguments,
        );

        return vsprintf($this->translate($translationKey, $translateToWelsh), $escaped);
    }

    /**
     * Format a translation string
     *
     * @param string $format
     * @param array $messages
     * @return string
     *
     * @psalm-suppress NoValue
     */
    public function formatTranslation($format, $messages)
    {
        if (!is_array($messages)) {
            return $this->wrapTranslation($format, $messages);
        }

        array_walk(
            $messages,
            function (&$value) {
                $value = $this->translate($value);
            }
        );

        return vsprintf($format, $messages);
    }

    /**
     * Wrap a translated message with the wrapper
     *
     * @param string $wrapper
     * @param string $message
     * @return string
     */
    public function wrapTranslation($wrapper, $message)
    {
        return sprintf($wrapper, $this->translate($message));
    }

    /**
     * Translate a message
     *
     * @param string $message
     * @return string
     */
    public function translate($message, $translateToWelsh = 'N')
    {
        $locale = ($translateToWelsh === 'Y') ? 'cy_GB' : null;
        return $this->translator->translate($message, 'default', $locale);
    }

    /**
     * Get translator
     *
     * @return TranslatorInterface
     */
    public function getTranslator()
    {
        return $this->translator;
    }
}
