<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\System;

use Dvsa\Olcs\Api\Domain\CommandHandler\System\DeleteFinancialStandingRateList as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\FinancialStandingRate as Repo;
use Dvsa\Olcs\Api\Entity\System\FinancialStandingRate as Entity;
use Dvsa\Olcs\Transfer\Command\System\DeleteFinancialStandingRateList as Command;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * DeleteFinancialStandingRateList Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class DeleteFinancialStandingRateListTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('FinancialStandingRate', Repo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => [69, 99]
        ];
        $command = Command::create($data);

        $mockRate1 = m::mock(Entity::class)
            ->shouldReceive('getId')
            ->andReturn(69)
            ->getMock();

        $mockRate2 = m::mock(Entity::class)
            ->shouldReceive('getId')
            ->andReturn(99)
            ->getMock();

        $this->repoMap['FinancialStandingRate']
            ->shouldReceive('fetchByIds')
            ->once()
            ->andReturn([$mockRate1, $mockRate2])
            ->shouldReceive('delete')
            ->with($mockRate1)
            ->once()
            ->shouldReceive('delete')
            ->with($mockRate2)
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertEquals(
            [
                'Financial Standing Rate ID 69 deleted',
                'Financial Standing Rate ID 99 deleted',
            ],
            $result->getMessages()
        );
    }
}
