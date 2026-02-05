<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\TranslationKey;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\TranslationKey\Delete as DeleteHandler;
use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\TranslationKey as TranslationKeyRepo;
use Dvsa\Olcs\Api\Entity\System\TranslationKey as TranslationKeyEntity;
use Dvsa\Olcs\Transfer\Command\TranslationKey\Delete as DeleteCmd;
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
        $this->mockRepo('TranslationKey', TranslationKeyRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 'TEST_STR_ID';

        $cmdData = ['id' => $id];
        $command = DeleteCmd::create($cmdData);

        $entity = m::mock(TranslationKeyEntity::class);

        $this->repoMap['TranslationKey']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($entity);

        $entity->shouldReceive('canDelete')
            ->once()
            ->withNoArgs()
            ->andReturn(true);

        $this->repoMap['TranslationKey']
            ->shouldReceive('delete')
            ->with($entity)
            ->once();

        $cacheResult = new Result();
        $cacheResult->addMessage('Generate cache result message');
        $this->expectedSideEffect(GenerateCache::class, [], $cacheResult);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'id' => 'TEST_STR_ID'
            ],
            'messages' => [
                "Translation Key $id Deleted",
                'Generate cache result message'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandCantDelete(): void
    {
        $id = 'TEST_STR_ID';

        $cmdData = ['id' => $id];
        $command = DeleteCmd::create($cmdData);
        $entity = m::mock(TranslationKeyEntity::class);

        $this->repoMap['TranslationKey']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andReturn($entity);

        $entity->shouldReceive('canDelete')
            ->once()
            ->withNoArgs()
            ->andReturn(false);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('editable-translations-cant-delete-with-texts');

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandCantFind(): void
    {
        $id = 'TEST_STR_ID';

        $cmdData = ['id' => $id];
        $command = DeleteCmd::create($cmdData);

        $this->repoMap['TranslationKey']
            ->shouldReceive('fetchById')
            ->with($id)
            ->once()
            ->andThrow(NotFoundException::class, 'Id TEST_STR_ID not found');

        $this->expectException(NotFoundException::class);
        $this->expectExceptionMessage('Id TEST_STR_ID not found');

        $this->sut->handleCommand($command);
    }
}
