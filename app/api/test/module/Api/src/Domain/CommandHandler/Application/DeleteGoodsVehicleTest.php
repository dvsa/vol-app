<?php

declare(strict_types=1);

/**
 * Delete Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\DeleteGoodsVehicle;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Command\Application\DeleteGoodsVehicle as Cmd;
use Dvsa\Olcs\Transfer\Command\Vehicle\DeleteLicenceVehicle as VehicleCmd;

/**
 * Delete Goods Vehicle Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DeleteGoodsVehicleTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new DeleteGoodsVehicle();
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
            'ids' => [123, 456]
        ];
        $command = Cmd::create($data);

        $data = [
            'ids' => [123, 456]
        ];
        $result1 = new Result();
        $result1->addMessage('Goods Vehicle Deleted');
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
                'Goods Vehicle Deleted',
                'Section Updated'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }
}
