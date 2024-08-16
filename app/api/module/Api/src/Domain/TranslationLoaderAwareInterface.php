<?php

namespace Dvsa\Olcs\Api\Domain;

use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;

/**
 * Translation Loader Aware Interface
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
interface TranslationLoaderAwareInterface
{
    /**
     * @return void
     */
    public function setTranslationLoader(TranslationLoader $translationLoader): void;

    /**
     * @return TranslationLoader
     */
    public function getTranslationLoader(): TranslationLoader;
}
