<?php

declare(strict_types=1);

/**
 * Create Bus Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus;

use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\CreateBus;
use Dvsa\Olcs\Transfer\Command\Bus\CreateBus as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Bus Test
 */
class CreateBusTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateBus();
        $this->mockRepo('Bus', BusRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            BusRegEntity::STATUS_NEW,
            BusRegEntity::SUBSIDY_NO,
        ];

        $this->references = [
            Licence::class => [
                11 => m::mock(Licence::class)->shouldReceive('getLatestBusRouteNo')->once()->andReturn(0)->getMock()
            ],
            BusNoticePeriod::class => [
                BusNoticePeriod::NOTICE_PERIOD_OTHER => m::mock(BusNoticePeriod::class)
            ]
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $licenceId = 11;
        $busRegId = 111;

        $command = Cmd::create(['licence' => $licenceId]);

        /** @var BusRegEntity $savedBusReg */
        $savedBusReg = null;

        $this->repoMap['Bus']->shouldReceive('save')
            ->once()
            ->with(m::type(BusRegEntity::class))
            ->andReturnUsing(
                function (BusRegEntity $busReg) use (&$savedBusReg) {
                    $busReg->setId(111);
                    $savedBusReg = $busReg;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'bus' => $busRegId
            ],
            'messages' => [
                'Bus created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
