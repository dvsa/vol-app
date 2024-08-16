<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\PresidingTc;

use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\PresidingTc\Update;
use Dvsa\Olcs\Api\Domain\Repository\PresidingTc as Repo;
use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @see Update
 */
class UpdateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Update();
        $this->mockRepo('PresidingTc', Repo::class);
        $this->mockRepo('User', UserRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 3,
            'name' => 'name',
            'user' => 5
        ];

        $command = \Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Update::create($data);

        $mockUser = m::mock(UserEntity::class);
        $this->repoMap['User']->shouldReceive('fetchById')->with(5)->once()->andReturn($mockUser);

        $entity = m::mock(PresidingTc::class);

        $this->repoMap['PresidingTc']
            ->shouldReceive('fetchUsingId')
            ->andReturn($entity);

        $entity->shouldReceive('update');

        $entity->shouldReceive('getId')->twice()->andReturn(3);

        $this->repoMap['PresidingTc']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PresidingTc::class))
            ->andReturn($entity)
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['PresidingTc' => 3],
            'messages' => ["PresidingTc '3' updated"]
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
