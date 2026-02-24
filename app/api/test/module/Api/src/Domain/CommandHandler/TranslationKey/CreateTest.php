<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\Exception;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKey\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as TranslationKeyRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Update as UpdateCmd;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Create as CreateCmd;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as TranslationKeyEntity;

/**
 * Create TranslationKey Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('TranslationKey', TranslationKeyRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 'TEST_STR_ID';
        $description = 'description';
        $translationsArray = [
            'en_GB' => base64_encode('English'),
            'cy_GB' => base64_encode('Welsh'),
            'en_NI' => base64_encode('English (NI)'),
            'cy_NI' => base64_encode('Welsh (NI)')
        ];

        $cmdData = [
            'translationKey' => $id,
            'description' => $description,
            'translationsArray' => $translationsArray
        ];

        $command = CreateCmd::create($cmdData);

        $this->repoMap['TranslationKey']
            ->shouldReceive('save')
            ->with(m::type(TranslationKeyEntity::class))
            ->once()
            ->andReturnUsing(
                function (TranslationKeyEntity $translationKey) {
                    $this->assertSame('TEST_STR_ID', $translationKey->getTranslationKey());
                    $this->assertSame('description', $translationKey->getDescription());
                    $translationKey->setId(4095);
                }
            );

        $this->expectedSideEffect(
            UpdateCmd::class,
            [
                'id' => 4095,
                'translationsArray' => $translationsArray
            ],
            new Result(),
            1
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'TranslationKey' => 'TEST_STR_ID'
            ],
            'messages' => [
                'TranslationKey created',
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandException(): void
    {
        $id = 'TEST_STR_ID';
        $description = 'description';
        $translationsArray = [
            'en_GB' => base64_encode('English'),
            'cy_GB' => base64_encode('Welsh'),
            'en_NI' => base64_encode('English (NI)'),
            'cy_NI' => base64_encode('Welsh (NI)')
        ];

        $cmdData = [
            'translationKey' => $id,
            'description' => $description,
            'translationsArray' => $translationsArray
        ];

        $command = CreateCmd::create($cmdData);

        $this->repoMap['TranslationKey']
            ->shouldReceive('save')
            ->with(m::type(TranslationKeyEntity::class))
            ->once()
            ->andThrow(Exception::class);

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('editable-translations-cant-save');

        $this->sut->handleCommand($command);
    }
}
