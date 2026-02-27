<?php

declare(strict_types=1);

/**
 * Update Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateGoodsVehicle;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Command\Application\UpdateGoodsVehicle as Cmd;
use Dvsa\Olcs\Transfer\Command\Vehicle\UpdateGoodsVehicle as VehicleCmd;

/**
 * Update Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class UpdateGoodsVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateGoodsVehicle();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [];
        $this->references = [];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'application' => 111,
            'id' => 123,
            'version' => 1,
            'platedWeight' => 100,
            'receivedDate' => null,
            'specifiedDate' => null,
            'removalDate' => null
        ];
        $command = Cmd::create($data);

        $data = [
            'id' => 123,
            'version' => 1,
            'platedWeight' => 100,
            'receivedDate' => null,
            'specifiedDate' => null,
            'removalDate' => null
        ];
        $result1 = new Result();
        $result1->addMessage('Goods Vehicle Updated');
        $this->expectedSideEffect(VehicleCmd::class, $data, $result1);

        $data = [
            'id' => 111,
            'section' => 'vehicles'
        ];
        $result2 = new Result();
        $result2->addMessage('Section Updated');
        $this->expectedSideEffect(UpdateApplicationCompletion::class, $data, $result2);

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);

        $expected = [
            'id' => [],
            'messages' => [
                'Goods Vehicle Updated',
                'Section Updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
