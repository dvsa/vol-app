<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\UpdateFeeStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\UpdateFeeStatus;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Update Fee Status Test
 */
class UpdateFeeStatusTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)->makePartial(),
        ];

        $this->sut = new UpdateFeeStatus();
        $this->mockRepo('Fee', Repository\FeeType::class);

        /** @var UserEntity $mockUser */
        $mockUser = m::mock(UserEntity::class)
            ->shouldReceive('getLoginId')
            ->andReturn('bob')
            ->getMock();

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create([
            'id' => 1,
            'status' => Fee::STATUS_REFUNDED
        ]);

        $mockFee = m::mock(Fee::class);
        $mockFee->shouldReceive('setFeeStatus')->once();
        $mockFee->shouldReceive('getId')->once()->andReturn(1);

        $this->repoMap['Fee']
            ->shouldReceive('fetchById')
            ->once()
            ->with($command->getId())
            ->andReturn($mockFee);

        $this->repoMap['Fee']->shouldReceive('save')->with($mockFee);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 1
            ],
            'messages' => [
                'Fee status updated:lfs_refunded'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
