<?php

namespace Dvsa\Olcs\Api\Domain;

use Laminas\I18n\Translator\TranslatorInterface;

/**
 * TranslatorAwareInterface
 */
interface TranslatorAwareInterface
{
    public function setTranslator(TranslatorInterface $translator);

    /**
     * @return TranslatorInterface
     */
    public function getTranslator();

    /**
     * @param string $message
     * @return string
     */
    public function translate($message);
}
