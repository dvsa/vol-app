<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TranslationKeyText;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKeyText\Delete as DeleteHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKeyText as TranslationKeyTextRepo;
use Dvsa\Olcs\Api\Entity\System\TranslationKeyText as TranslationKeyTextEntity;
use Dvsa\Olcs\Transfer\Command\TranslationKeyText\Delete as DeleteCmd;
use Dvsa\Olcs\Transfer\Command\TranslationKey\GenerateCache;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Delete TranslationKey Test
 *
 * @author Andy Newton <andy@vitri.ltd>
 */
class DeleteTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteHandler();
        $this->mockRepo('TranslationKeyText', TranslationKeyTextRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 2;

        $cmdData = ['id' => $id];
        $command = DeleteCmd::create($cmdData);

        $entity = m::mock(TranslationKeyTextEntity::class);

        $this->repoMap['TranslationKeyText']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($entity);

        $this->repoMap['TranslationKeyText']
            ->shouldReceive('delete')
            ->with($entity)
            ->once();

        $cacheResult = new Result();
        $cacheResult->addMessage('Generate cache result message');
        $this->expectedSideEffect(GenerateCache::class, [], $cacheResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'id' => 2
            ],
            'messages' => [
                "Translation Key Text $id Deleted",
                'Generate cache result message'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCantFind(): void
    {
        $id = 2;

        $cmdData = ['id' => $id];
        $command = DeleteCmd::create($cmdData);

        $this->repoMap['TranslationKeyText']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andThrow(NotFoundException::class, 'Id 2 not found');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Id 2 not found');

        $this->sut->handleCommand($command);
    }
}
