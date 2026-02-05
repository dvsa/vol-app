<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TranslationKeyText;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKeyText\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as TranslationKeyRepo;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as TranslationKeyTextRepo;
use Dvsa\Olcs\Api\Domain\Repository\Language as LanguageRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\TranslationKeyText\Create as CreateCmd;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as TranslationKeyEntity;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText as TranslationKeyTextEntity;
use Dvsa\Olcs\Api\Entity\System\Language as LanguageEntity;

/**
 * Create TranslationKeyText Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('TranslationKey', TranslationKeyRepo::class);
        $this->mockRepo('TranslationKeyText', TranslationKeyTextRepo::class);
        $this->mockRepo('Language', LanguageRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $translationKey = 'TEST_STR_ID';
        $language = 1;
        $translatedText = 'This is some text';

        $cmdData = [
            'translationKey' => $translationKey,
            'language' => $language,
            'translatedText' => $translatedText,
        ];

        $command = CreateCmd::create($cmdData);

        $entity = m::mock(TranslationKeyTextEntity::class)->makePartial();
        $entity->setId(555);

        $translationKeyEntity = m::mock(TranslationKeyEntity::class);
        $languageEntity = m::mock(LanguageEntity::class);

        $this->repoMap['TranslationKey']
            ->shouldReceive('fetchById')
            ->with($translationKey)
            ->once()
            ->andReturn($translationKeyEntity);

        $this->repoMap['Language']
            ->shouldReceive('fetchById')
            ->with($language)
            ->once()
            ->andReturn($languageEntity);

        $entity->shouldReceive('getId')
            ->andReturn(55);

        $this->repoMap['TranslationKeyText']
            ->shouldReceive('save')
            ->with(m::type(TranslationKeyTextEntity::class))
            ->once()
            ->andReturnUsing(
                function (TranslationKeyTextEntity $transKeyText) use ($languageEntity, $translationKeyEntity, $translatedText) {
                    $transKeyText->setId(555);
                    $this->assertEquals($translatedText, $transKeyText->getTranslatedText());
                    $this->assertEquals($languageEntity, $transKeyText->getLanguage());
                    $this->assertEquals($translationKeyEntity, $transKeyText->getTranslationKey());
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'TranslationKeyText' => 555
            ],
            'messages' => [
                'Translation Key Text Created: 555'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
