<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Cases\PresidingTc;

use Dvsa\Olcs\Api\Domain\Repository\User as UserRepo;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Cases\PresidingTc\Create as Create;
use Dvsa\Olcs\Api\Domain\Repository\PresidingTc as PresidingTcRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\Cases\PresidingTc\Create as Cmd;
use Dvsa\Olcs\Api\Entity\Pi\PresidingTc;

/**
 * @see Create
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Create();
        $this->mockRepo('PresidingTc', PresidingTcRepo::class);
        $this->mockRepo('User', UserRepo::class);
        parent::setUp();
    }

    /** @SuppressWarnings("unused") */
    public function testHandleCommand(): void
    {
        $data = [
            'name' => 'name',
            'user' => 2
        ];

        $command = Cmd::create($data);

        $mockUser = m::mock(UserEntity::class);
        $this->repoMap['User']->shouldReceive('fetchById')->with(2)->once()->andReturn($mockUser);

        $this->repoMap['PresidingTc']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PresidingTc::class))
            ->andReturnUsing(
                function (PresidingTc $ptc) use (&$presidingTc) {
                    $ptc->setid(1);
                    $presidingTc = $ptc;
                }
            )
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['PresidingTc' => 1],
            'messages' => ["PresidingTc '1' created"]
        ];
        $this->assertEquals($expected, $result->toArray());
    }
}
