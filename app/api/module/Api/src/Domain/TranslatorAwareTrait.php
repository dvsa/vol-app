<?php

namespace Dvsa\Olcs\Api\Domain;

use Laminas\I18n\Translator\Translator;
use Laminas\I18n\Translator\TranslatorInterface;

/**
 * Translator Aware
 */
trait TranslatorAwareTrait
{
    /**
     * @var TranslatorInterface|Translator
     */
    protected $translator;

    public function setTranslator(TranslatorInterface $service)
    {
        $this->translator = $service;
    }

    /**
     * @return TranslatorInterface|Translator
     */
    public function getTranslator()
    {
        return $this->translator;
    }

    /**
     * @param string
     * @return string
     */
    public function translate($message)
    {
        return $this->getTranslator()->translate($message);
    }
}
