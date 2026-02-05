<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\Command\Discs\CreatePsvVehicleListForDiscs as CreatePsvVehicleListForDiscsCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\CommandHandler\Discs\BatchVehicleListGeneratorForPsvDiscs as Batch;
use Dvsa\Olcs\Api\Domain\Command\Discs\BatchVehicleListGeneratorForPsvDiscs as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Entity\Queue\Queue;

/**
 * BatchVehicleListGeneratorForPsvDiscs test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class BatchVehicleListGeneratorForPsvDiscsTest extends AbstractCommandHandlerTestCase
{
    protected $batchSize = 180;

    protected $additionalSize = 2;

    public function setUp(): void
    {
        $this->sut = new Batch();

        $this->mockedSmServices = [
            'config' => [
                'disc_printing' => ['psv_vehicle_list_batch_size' => $this->batchSize]
            ]
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $queries = $this->getQueries();
        $bookmarks = $this->getBookmarks();
        $data = [
            'queries' => $queries,
            'bookmarks' => $bookmarks,
            'user' => 1
        ];
        $queuedQueries = array_slice($queries, $this->batchSize, count($queries) - $this->additionalSize, true);
        $options = [
            'queries' => $queuedQueries,
            'bookmarks' => $bookmarks,
            'user' => 1
        ];

        $command = Cmd::create($data);
        $expected = ['id' => []];

        for ($i = 0; $i < $this->batchSize; $i++) {
            $licenceId = $i + 1;
            $data = [
                'id' =>  $licenceId,
                'knownValues' => ['foo' => 'bar' . $licenceId],
                'user' => 1
            ];
            $this->expectedSideEffect(CreatePsvVehicleListForDiscsCommand::class, $data, new Result());
            $expected['messages'][] = 'Vehicle list generated for licence ' . $licenceId;
        }

        $data = [
            'type' => Queue::TYPE_CREATE_PSV_VEHICLE_LIST,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $this->expectedSideEffect(CreatQueue::class, $data, new Result());

        $result = $this->sut->handleCommand($command);
        $this->assertEquals($expected, $result->toArray());
    }

    protected function getBookmarks(): mixed
    {
        $params = [];
        for ($i = 0; $i < $this->batchSize + $this->additionalSize; $i++) {
            $params[$i] = ['foo' => 'bar' . $i];
        }
        return $params;
    }

    protected function getQueries(): mixed
    {
        $queries = [];
        for ($i = 0; $i < $this->batchSize + $this->additionalSize; $i++) {
            $licenceId = $i + 1;
            $queries[$licenceId] = ['id' => $licenceId];
        }
        return $queries;
    }
}
