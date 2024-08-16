<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;

/**
 * Translation Loader Aware Trait
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
trait TranslationLoaderAwareTrait
{
    /** @var TranslationLoader */
    protected $translationLoader;

    /**
     * set translation loader
     *
     *
     * @return void
     */
    public function setTranslationLoader(TranslationLoader $translationLoader): void
    {
        $this->translationLoader = $translationLoader;
    }

    /**
     * get translation loader
     *
     * @return TranslationLoader
     */
    public function getTranslationLoader(): TranslationLoader
    {
        return $this->translationLoader;
    }
}
