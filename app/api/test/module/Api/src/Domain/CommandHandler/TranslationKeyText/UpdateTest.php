<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TranslationKeyText;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKeyText\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as TranslationKeyTextRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\TranslationKeyText\Update as UpdateCmd;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText as TranslationKeyTextEntity;

/**
 * Update TranslationKeyText Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('TranslationKeyText', TranslationKeyTextRepo::class);
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 232;
        $translatedText = 'This is some text';

        $cmdData = [
            'id' => $id,
            'translatedText' => $translatedText,
        ];

        $command = UpdateCmd::create($cmdData);

        $entity = m::mock(TranslationKeyTextEntity::class);

        $this->repoMap['TranslationKeyText']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($entity);

        $entity->shouldReceive('update')
            ->globally()
            ->ordered()
            ->once()
            ->with($translatedText);

        $this->repoMap['TranslationKeyText']
            ->shouldReceive('save')
            ->globally()
            ->ordered()
            ->with($entity)
            ->once();

        $entity->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(232);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'TranslationKeyText' => 232
            ],
            'messages' => [
                'Translation Key Text Updated: 232'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
