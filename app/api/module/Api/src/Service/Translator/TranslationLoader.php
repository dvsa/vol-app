<?php

namespace Dvsa\Olcs\Api\Service\Translator;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as TranslationKeyTextRepo;
use Dvsa\Olcs\Api\Domain\Repository\Replacement as ReplacementRepo;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Olcs\Logging\Log\Logger;
use Laminas\I18n\Translator\Loader\PhpMemoryArray;
use Laminas\I18n\Translator\Loader\RemoteLoaderInterface;
use Laminas\I18n\Translator\TextDomain;

/**
 * Translation loader service for API nodes
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationLoader implements RemoteLoaderInterface
{
    public const ERR_CACHE_LOAD = 'Translation cache load failure: %s';
    public const DEFAULT_TEXT_DOMAIN = 'default';

    /**
     * TranslationLoader constructor.
     */
    public function __construct(private readonly CacheEncryption $cache, private readonly TranslationKeyTextRepo $translationKeyRepo, private readonly ReplacementRepo $replacementRepo)
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
    public function load($locale, $textDomain)
    {
        $messages = $this->getMessages($locale, $textDomain);
        $phpMemoryArray = new PhpMemoryArray($messages);

        return $phpMemoryArray->load($locale, $textDomain);
    }

    /**
     * Get translation messages, try the cache first, fall back to the DB
     *
     *
     * @return array
     */
    public function getMessages(string $locale, string $textDomain): array
    {
        try {
            $messages = $this->cache->getCustomItem(CacheEncryption::TRANSLATION_KEY_IDENTIFIER, $locale);
        } catch (\Exception $e) {
            $messages = null;
            $errorMessage = sprintf(self::ERR_CACHE_LOAD, $e->getMessage());
            Logger::err($errorMessage);
        }

        //if there has been a problem with the Redis cache, fall back to the database, repopulate cache
        if (!$messages) {
            $messages = $this->getMessagesFromDb($locale, $textDomain);
        }

        return $messages;
    }

    /**
     * Get translation messages from the database
     *
     *
     * @return array
     */
    public function getMessagesFromDb(string $locale, string $textDomain): array
    {
        $translationKeys = $this->translationKeyRepo->fetchAll($locale, Query::HYDRATE_ARRAY);

        $messages = [];

        foreach ($translationKeys as $key) {
            $messages[$textDomain][$key['language']['isoCode']][$key['translationKey']['translationKey']] = $key['translatedText'];
        }

        return $messages;
    }

    /**
     * Load translation replacements, try the cache first, fall back to the DB
     *
     * @return array
     */
    public function loadReplacements(): array
    {
        try {
            $replacements = $this->cache->getCustomItem(CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER);
        } catch (\Exception $e) {
            $replacements = null;
            $errorMessage = sprintf(self::ERR_CACHE_LOAD, $e->getMessage());
            Logger::err($errorMessage);
        }

        //if there has been a problem with the Redis cache, fall back to the database, repopulate cache
        if (!$replacements) {
            $replacements = $this->getReplacementsFromDb();
        }

        return $replacements;
    }

    /**
     * Get translation replacements from the database
     *
     * @return array
     */
    public function getReplacementsFromDb(): array
    {
        $replacements = [];
        $dbReplacements = $this->replacementRepo->fetchAll(Query::HYDRATE_ARRAY);

        foreach ($dbReplacements as $replacement) {
            $replacements[$replacement['placeholder']] = $replacement['replacementText'];
        }

        return $replacements;
    }
}
