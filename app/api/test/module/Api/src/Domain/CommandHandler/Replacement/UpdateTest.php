<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Replacement;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Replacement\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\Replacement as ReplacementRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Replacement\Update as UpdateCmd;
use Dvsa\Olcs\Api\Entity\System\Replacement as ReplacementEntity;

/**
 * Update Replacement Test
 *
 * @author Andy Newton <andy@vitri.ltd
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('Replacement', ReplacementRepo::class);
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'id' => '1',
            'placeholder' => '{{something}}',
            'replacementText' => 'UK'
        ];

        $command = UpdateCmd::create($cmdData);

        $entity = m::mock(ReplacementEntity::class);

        $entity->shouldReceive('update')
            ->with(
                $cmdData['placeholder'],
                $cmdData['replacementText']
            )
            ->globally()
            ->ordered()
            ->once();

        $entity->shouldReceive('getId')
            ->twice()
            ->andReturn($cmdData['id']);

        $this->repoMap['Replacement']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($entity);

        $this->repoMap['Replacement']
            ->shouldReceive('save')
            ->once()
            ->globally()
            ->ordered()
            ->with($entity);

        $this->expectedCacheSideEffect(CacheEncryption::TRANSLATION_REPLACEMENT_IDENTIFIER);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['Replacement' => $cmdData['id']],
            'messages' => [
                "Replacement '" . $cmdData['id'] . "' updated",
                'cache update message',
            ],
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
