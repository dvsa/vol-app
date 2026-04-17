<?php

declare(strict_types=1);

/**
 * UpdateGracePeriod.php
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\GracePeriod;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\GracePeriod as GracePeriodRepo;
use Dvsa\Olcs\Api\Domain\CommandHandler\GracePeriod\UpdateGracePeriod;
use Dvsa\Olcs\Api\Entity\Licence\GracePeriod;
use Dvsa\Olcs\Transfer\Command\GracePeriod\UpdateGracePeriod as Cmd;

/**
 * Class UpdateGracePeriod
 *
 * @package Dvsa\OlcsTest\Api\Domain\CommandHandler\GracePeriod
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class UpdateGracePeriodTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateGracePeriod();
        $this->mockRepo('GracePeriod', GracePeriodRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
            'startDate' => '2015-01-01',
            'endDate' => '2015-01-02',
            'description' => 'description'
        ];

        $command = Cmd::create($data);

        $this->repoMap['GracePeriod']
            ->shouldReceive('fetchById')
            ->once()
            ->andReturn(
                m::mock(GracePeriod::class)
                    ->shouldReceive('setStartDate')
                    ->once()
                    ->shouldReceive('setEndDate')
                    ->once()
                    ->shouldReceive('setDescription')
                    ->once()
                    ->shouldReceive('getId')
                    ->once()
                    ->getMock()
            )
            ->shouldReceive('save')
            ->once()
            ->with(m::type(GracePeriod::class));

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'graceperiod' => null
            ],
            'messages' => [
                'Grace period updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
