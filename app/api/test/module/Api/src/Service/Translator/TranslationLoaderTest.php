<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Translator;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Replacement;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Laminas\I18n\Translator\TextDomain;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Logging\Log\Logger;

/**
 * TranslationLoaderTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class TranslationLoaderTest extends MockeryTestCase
{
    public function setUp(): void
    {
        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);
    }

    /**
     * test loading translations from the cache
     */
    public function testLoadTranslationsFromCache(): void
    {
        $locale = 'en_GB';
        $textDomain = 'default';
        $actualMessages = ['some_key' => 'some_text'];
        $cacheIdentifier = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;

        $messages = [
            $textDomain => [
                $locale => $actualMessages,
            ],
        ];

        $mockCache = m::mock(CacheEncryption::class);
        $mockCache->expects('getCustomItem')
            ->with($cacheIdentifier, $locale)
            ->andReturn($messages);

        $mockTranslationTextRepo = m::mock(TranslationKeyText::class);
        $mockReplacementRepo = m::mock(Replacement::class);

        $loader = new TranslationLoader($mockCache, $mockTranslationTextRepo, $mockReplacementRepo);
        $textDomain = $loader->load($locale, $textDomain);

        self::assertInstanceOf(TextDomain::class, $textDomain);
        self::assertSame($actualMessages, $textDomain->getArrayCopy());
    }

    /**
     * test loading translations from the database it the cache is empty
     */
    public function testLoadTranslationsFromDatabase(): void
    {
        $locale = 'en_GB';
        $textDomain = 'default';
        $cacheIdentifier = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;

        $dbTranslations = $this->dbTranslations($locale);

        $mockCache = m::mock(CacheEncryption::class);
        $mockCache->expects('getCustomItem')
            ->with($cacheIdentifier, $locale)
            ->andReturnNull();

        $mockTranslationTextRepo = m::mock(TranslationKeyText::class);
        $mockTranslationTextRepo->expects('fetchAll')
            ->with($locale, Query::HYDRATE_ARRAY)
            ->andReturn($dbTranslations);
        $mockReplacementRepo = m::mock(Replacement::class);

        $loader = new TranslationLoader($mockCache, $mockTranslationTextRepo, $mockReplacementRepo);
        $textDomain = $loader->load($locale, $textDomain);

        self::assertInstanceOf(TextDomain::class, $textDomain);
        self::assertSame($this->actualTranslations(), $textDomain->getArrayCopy());
    }

    /**
     * test loading translations from the database when cache is down (exception on cache load)
     */
    public function testCacheExceptionsStillLoadTranslationsFromDatabase(): void
    {
        $locale = 'en_GB';
        $textDomain = 'default';
        $cacheIdentifier = CacheEncryption::TRANSLATION_KEY_IDENTIFIER;

        $dbTranslations = $this->dbTranslations($locale);

        $mockCache = m::mock(CacheEncryption::class);
        $mockCache->expects('getCustomItem')
            ->with($cacheIdentifier, $locale)
            ->andThrow(\Exception::class);

        $mockTranslationTextRepo = m::mock(TranslationKeyText::class);
        $mockTranslationTextRepo->expects('fetchAll')
            ->with($locale, Query::HYDRATE_ARRAY)
            ->andReturn($dbTranslations);
        $mockReplacementRepo = m::mock(Replacement::class);

        $loader = new TranslationLoader($mockCache, $mockTranslationTextRepo, $mockReplacementRepo);
        $textDomain = $loader->load($locale, $textDomain);

        self::assertInstanceOf(TextDomain::class, $textDomain);
        self::assertSame($this->actualTranslations(), $textDomain->getArrayCopy());
    }

    /**
     * test loading replacements from the cache
     */
    public function testLoadReplacementsFromCache(): void
    {
        $replacements = ['replacements'];

        $mockCache = m::mock(CacheEncryption::class);
        $mockCache->expects('getCustomItem')
            ->with(CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER)
            ->andReturn($replacements);

        $mockTranslationTextRepo = m::mock(TranslationKeyText::class);
        $mockReplacementRepo = m::mock(Replacement::class);

        $loader = new TranslationLoader($mockCache, $mockTranslationTextRepo, $mockReplacementRepo);

        self::assertSame($replacements, $loader->loadReplacements());
    }

    /**
     * test loading replacements from the cache
     */
    public function testLoadReplacementsFromDatabase(): void
    {
        $mockCache = m::mock(CacheEncryption::class);
        $mockCache->expects('getCustomItem')
            ->with(CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER)
            ->andReturnNull();

        $mockTranslationTextRepo = m::mock(TranslationKeyText::class);
        $mockReplacementRepo = m::mock(Replacement::class);
        $mockReplacementRepo->expects('fetchAll')
            ->with(Query::HYDRATE_ARRAY)
            ->andReturn($this->dbReplacements());

        $loader = new TranslationLoader($mockCache, $mockTranslationTextRepo, $mockReplacementRepo);

        self::assertSame($this->actualReplacements(), $loader->loadReplacements());
    }

    /**
     * test loading replacements from the database when cache is down (exception on cache load)
     */
    public function testCacheExceptionsStillLoadReplacementsFromDatabase(): void
    {
        $mockCache = m::mock(CacheEncryption::class);
        $mockCache->expects('getCustomItem')
            ->with(CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER)
            ->andThrow(\Exception::class);

        $mockTranslationTextRepo = m::mock(TranslationKeyText::class);
        $mockReplacementRepo = m::mock(Replacement::class);
        $mockReplacementRepo->expects('fetchAll')
            ->with(Query::HYDRATE_ARRAY)
            ->andReturn($this->dbReplacements());

        $loader = new TranslationLoader($mockCache, $mockTranslationTextRepo, $mockReplacementRepo);

        self::assertSame($this->actualReplacements(), $loader->loadReplacements());
    }

    private function dbTranslations(mixed $locale): array
    {
        return [
            0 => [
                'language' => [
                    'isoCode' => $locale,
                ],
                'translationKey' => [
                    'translationKey' => 'translation_key1',
                ],
                'translatedText' => 'translated_text1',
            ],
            1 => [
                'language' => [
                    'isoCode' => $locale,
                ],
                'translationKey' => [
                    'translationKey' => 'translation_key2',
                ],
                'translatedText' => 'translated_text2',
            ],
        ];
    }

    private function actualTranslations(): array
    {
        return [
            'translation_key1' => 'translated_text1',
            'translation_key2' => 'translated_text2'
        ];
    }

    private function dbReplacements(): array
    {
        return [
            0 => [
                'placeholder' => 'placeholder1',
                'replacementText' => 'replacementText1',
            ],
            1 => [
                'placeholder' => 'placeholder2',
                'replacementText' => 'replacementText2',
            ],
        ];
    }

    private function actualReplacements(): array
    {
        return [
            'placeholder1' => 'replacementText1',
            'placeholder2' => 'replacementText2',
        ];
    }
}
