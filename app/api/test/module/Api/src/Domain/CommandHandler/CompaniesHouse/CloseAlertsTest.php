<?php

declare(strict_types=1);

/**
 * Close Alerts Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\CompaniesHouse;

use Dvsa\Olcs\Api\Domain\CommandHandler\CompaniesHouse\CloseAlerts;
use Dvsa\Olcs\Api\Domain\Repository\CompaniesHouseAlert as AlertRepo;
use Dvsa\Olcs\Api\Entity\CompaniesHouse\CompaniesHouseAlert as AlertEntity;
use Dvsa\Olcs\Transfer\Command\CompaniesHouse\CloseAlerts as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Close Alerts Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class CloseAlertsTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CloseAlerts();
        $this->mockRepo('CompaniesHouseAlert', AlertRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $command = Cmd::create(['ids' => [123, 321]]);

        $alert1 = m::mock(AlertEntity::class);
        $alert1->shouldReceive('setIsClosed')->once()->with('Y');
        $alert2 = m::mock(AlertEntity::class);
        $alert2->shouldReceive('setIsClosed')->once()->with('Y');

        $this->repoMap['CompaniesHouseAlert']->shouldReceive('fetchById')
            ->with(123)
            ->andReturn($alert1)
            ->shouldReceive('fetchById')
            ->with(321)
            ->andReturn($alert2)
            ->shouldReceive('save')
            ->with($alert1)
            ->shouldReceive('save')
            ->with($alert2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '2 Alert(s) closed'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
