<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\System\Language;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\Olcs\Transfer\Command\TranslationKey\GenerateCache as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKey\GenerateCache as Handler;
use Dvsa\Olcs\Api\Service\Translator\TranslationLoader;
use Mockery as m;
use Laminas\Mvc\I18n\Translator;

/**
 * Test cache generation for each locale
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class GenerateCacheTest extends AbstractCommandHandlerTestCase
{
    private $translator;

    public function setUp(): void
    {
        $this->sut = new Handler();
        $this->translator = m::mock(Translator::class);

        $this->mockedSmServices = [
            'translator' => $this->translator,
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $expectedMessages = [];

        /**
         * For each locale that we support, create an assertion for the load, the save, and the cache delete
         */
        foreach (array_keys(Language::SUPPORTED_LANGUAGES) as $locale) {
            $updateCacheResult = new Result();
            $updateCacheResult->addMessage('updated ' . $locale);

            $this->expectedCacheSideEffect(
                CacheEncryption::TRANSLATION_KEY_IDENTIFIER,
                $locale,
                $updateCacheResult
            );

            $this->translator->expects('clearCache')->with(TranslationLoader::DEFAULT_TEXT_DOMAIN, $locale);

            $expectedMessages[] = 'updated ' . $locale;
            $expectedMessages[] = sprintf(Handler::UPDATE_MSG, $locale);
        }

        $command = Cmd::create([]);
        $result = $this->sut->handleCommand($command);

        $this->assertEquals($expectedMessages, $result->getMessages());
    }
}
