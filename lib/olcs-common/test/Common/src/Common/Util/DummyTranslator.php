<?php

namespace CommonTest\Common\Util;

/**
 * Dummy translator, useful for unit testing
 *
 * Takes an array of key=>value translations via setMap
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DummyTranslator implements \Laminas\I18n\Translator\TranslatorInterface
{
    protected $map = [];

    public function setMap(array $map): void
    {
        $this->map = $map;
    }

    /**
     * Translate a message.
     *
     * @param  string $message
     * @param  string $textDomain
     * @param  string $locale
     * @return string
     */
    #[\Override]
    public function translate($message, $textDomain = 'default', $locale = null)
    {
        if (array_key_exists($message, $this->map)) {
            return $this->map[$message];
        }

        return $message;
    }

    /**
     * Translate a plural message.
     *
     * @param  string      $singular
     * @param  string      $plural
     * @param  int         $number
     * @param  string      $textDomain
     * @param  string|null $locale
     * @return string
     */
    #[\Override]
    public function translatePlural(
        $singular,
        $plural,
        $number,
        $textDomain = 'default',
        $locale = null
    ) {
        return 'dummy plural string';
    }
}
