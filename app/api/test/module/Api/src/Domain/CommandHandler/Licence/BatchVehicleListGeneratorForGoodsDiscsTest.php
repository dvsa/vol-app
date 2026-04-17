<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Transfer\Command\Licence\CreateVehicleListDocument as CreateVehicleListDocumentCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\BatchVehicleListGeneratorForGoodsDiscs as Batch;
use Dvsa\Olcs\Api\Domain\Command\Licence\BatchVehicleListGeneratorForGoodsDiscs as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Entity\Queue\Queue;

/**
 * BatchVehicleListGeneratorForGoodsDiscs test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BatchVehicleListGeneratorForGoodsDiscsTest extends AbstractCommandHandlerTestCase
{
    protected $batchSize = 180;

    public function setUp(): void
    {
        $this->sut = new Batch();

        $this->mockedSmServices = [
            'config' => [
                'disc_printing' => ['gv_vehicle_list_batch_size' => $this->batchSize]
            ]
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $licences = $this->getLicences();
        $data = [
            'licences' => $licences,
            'user' => 1
        ];
        $queuedLicences = array_slice($licences, $this->batchSize);
        $options = [
            'licences' => $queuedLicences,
            'user' => 1
        ];

        $command = Cmd::create($data);
        $expected = ['id' => []];

        for ($i = 0; $i < $this->batchSize; $i++) {
            $data = [
                'id' =>  $i,
            ];
            $this->expectedSideEffect(CreateVehicleListDocumentCommand::class, $data, new Result());
            $expected['messages'][] = 'Vehicle list generated for licence ' . $i;
        }

        $data = [
            'type' => Queue::TYPE_CREATE_GOODS_VEHICLE_LIST,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $this->expectedSideEffect(CreatQueue::class, $data, new Result());

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    protected function getLicences(): mixed
    {
        $licences = [];
        for ($i = 0; $i < $this->batchSize + 2; $i++) {
            $licences[] = ['id' => $i];
        }
        return $licences;
    }
}
