<?php

namespace Common\Service\Translator;

use Common\Service\Cqrs\Query\CachingQueryService;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Olcs\Logging\Log\Logger;
use Laminas\I18n\Translator\Loader\PhpMemoryArray;
use Laminas\I18n\Translator\Loader\RemoteLoaderInterface;
use Laminas\I18n\Translator\TextDomain;

/**
 * Loads translations from the Redis cache, if cache unavailable this will fallback to the database
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationLoader implements RemoteLoaderInterface
{
    public const ERR_UNABLE_TO_LOAD_REPLACEMENTS = 'Replacements could not be loaded: %s';

    public const ERR_UNABLE_TO_LOAD = 'Translations could not be loaded: %s';

    /**
     * TranslationLoader constructor.
     *
     *
     * @return void
     */
    public function __construct(private CachingQueryService $queryService)
    {
    }

    /**
     * Load translation information based on the locale
     *
     * @param string $locale
     * @param string $textDomain needed to comply with interface but not needed by us
     *
     * @return TextDomain
     * @throws \Exception
     */
    #[\Override]
    public function load($locale, $textDomain)
    {
        try {
            $messages = $this->queryService->handleCustomCache(CacheEncryption::TRANSLATION_KEY_IDENTIFIER, $locale);
        } catch (\Exception $exception) {
            $message = sprintf(self::ERR_UNABLE_TO_LOAD, $exception->getMessage());
            throw new \Exception($message, $exception->getCode(), $exception);
        }

        $phpMemoryArray = new PhpMemoryArray($messages);
        return $phpMemoryArray->load($locale, $textDomain);
    }

    /**
     * Load translation replacements
     *
     * @throws \Exception
     */
    public function loadReplacements(): array
    {
        try {
            $replacements = $this->queryService->handleCustomCache(
                CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER
            );
        } catch (\Exception $exception) {
            $replacements = [];
            $errorMessage = sprintf(self::ERR_UNABLE_TO_LOAD_REPLACEMENTS, $exception->getMessage());
            Logger::err($errorMessage);
        }

        return $replacements;
    }
}
