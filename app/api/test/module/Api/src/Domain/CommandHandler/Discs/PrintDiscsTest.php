<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Discs;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\CommandHandler\Discs\PrintDiscs;
use Dvsa\Olcs\Api\Domain\Command\Discs\PrintDiscs as Cmd;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList as Qry;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Api\Domain\Command\Queue\Create as CreatQueue;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Api\Domain\Repository\GoodsDisc as GoodsDiscRepo;

/**
 * Print discs
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PrintDiscsTest extends AbstractCommandHandlerTestCase
{
    protected $batchSize = 180;

    public function setUp(): void
    {
        $this->sut = new PrintDiscs();
        $this->mockRepo('GoodsDisc', GoodsDiscRepo::class);

        $this->mockedSmServices = [
            'config' => [
                'disc_printing' => ['disc_batch_size' => $this->batchSize]
            ]
        ];

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $startNumber = 1;
        $userId = 2;
        $discs = $this->getDiscs();
        $data = [
            'type' => 'Goods',
            'discs' => $discs,
            'startNumber' => $startNumber,
            'user' => $userId
        ];
        $command = Cmd::create($data);

        $queuedStartNumber = $startNumber + $this->batchSize;
        $queuedDiscs = array_slice($discs, $this->batchSize);
        $discs = array_slice($discs, 0, $this->batchSize);
        $options = [
            'discs' => $queuedDiscs,
            'startNumber' => $queuedStartNumber,
            'type' => 'Goods',
            'user' => $userId
        ];

        $generateAndStoreData = [
            'template' => 'GVDiscTemplate',
            'query' => array_merge($discs, ['user' => $userId]),
            'knownValues' => $this->getKnownValues($startNumber),
            'description' => 'Vehicle discs',
            'category' => CategoryEntity::CATEGORY_LICENSING,
            'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_DISCS,
            'isExternal' => false,
            'isScan' => false
        ];
        $documentResult = new Result();
        $documentResult->addId('document', 12);
        $this->expectedSideEffect(GenerateAndStore::class, $generateAndStoreData, $documentResult);

        $printQueueData = [
            'documentId' => 12,
            'jobName' => 'Goods Disc List',
            'user' => $userId
        ];
        $this->expectedSideEffect(EnqueueFileCommand::class, $printQueueData, new Result());

        $data = [
            'type' => Queue::TYPE_DISC_PRINTING,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $this->expectedSideEffect(CreatQueue::class, $data, new Result());

        $this->repoMap['GoodsDisc']
            ->shouldReceive('setIsPrintingOn')
            ->with($discs)
            ->once();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['document' => 12],
            'messages' => ['Discs printed']
        ];
        $this->assertEquals($expected, $result->toArray());
    }

    protected function getDiscs(): mixed
    {
        $discs = [];
        for ($i = 0; $i <= $this->batchSize +  2; $i++) {
            $discs[] = $i + 1;
        }
        return $discs;
    }

    protected function getKnownValues(mixed $startNumber): mixed
    {
        $knownValues = [
            'Disc_List' => []
        ];
        $discNumber = (int) $startNumber;
        for ($i = 0; $i < $this->batchSize; $i++) {
            $knownValues['Disc_List'][$i]['discNo'] = $discNumber++;
        }
        return $knownValues;
    }
}
