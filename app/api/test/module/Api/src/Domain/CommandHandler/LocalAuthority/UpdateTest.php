<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\LocalAuthority;

use Dvsa\Olcs\Api\Domain\CommandHandler\LocalAuthority\Update as UpdateHandler;
use Dvsa\Olcs\Api\Domain\Repository\LocalAuthority as LocalAuthorityRepo;
use Dvsa\Olcs\Api\Entity\Bus\LocalAuthority as LocalAuthorityEntity;
use Dvsa\Olcs\Transfer\Command\LocalAuthority\Update as UpdateCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Update Local Authority Test
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateHandler();
        $this->mockRepo('LocalAuthority', LocalAuthorityRepo::class);
        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $id = 999;
        $description = 'lta descr';
        $emailAddress = 'some@email.com';

        $cmdData = [
            'id' => $id,
            'description' => $description,
            'emailAddress' => $emailAddress,
        ];

        $command = UpdateCmd::create($cmdData);

        $entity = m::mock(LocalAuthorityEntity::class);
        $entity->shouldReceive('update')
            ->once()
            ->with($description, $emailAddress);
        $entity->shouldReceive('getId')
            ->twice()
            ->andReturn($id);

        $this->repoMap['LocalAuthority']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command)
            ->andReturn($entity);

        $this->repoMap['LocalAuthority']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(LocalAuthorityEntity::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['LocalAuthority' => $id],
            'messages' => ["Local Authority '" . $id . "' updated"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
