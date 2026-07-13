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
use Dvsa\Olcs\Api\Domain\Repository\PsvDisc as PsvDiscRepo;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;

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
        $this->mockRepo('PsvDisc', PsvDiscRepo::class);
        $this->mockRepo('SystemParameter', SystemParameterRepo::class);

        $this->mockedSmServices = [
            'config' => [
                'disc_printing' => ['disc_batch_size' => $this->batchSize]
            ]
        ];

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('pinnedLayoutProvider')]
    public function testHandleCommand(
        string $type,
        string $toggle,
        string $repo,
        string $bookmark,
        ?string $toggleValue,
        string $expectedTemplate
    ): void {
        $startNumber = 1;
        $userId = 2;
        $discs = $this->getDiscs();
        $data = [
            'type' => $type,
            'discs' => $discs,
            'startNumber' => $startNumber,
            'user' => $userId
        ];
        $command = Cmd::create($data);

        $this->repoMap['SystemParameter']
            ->shouldReceive('fetchIsEnabled')
            ->with($toggle)
            ->andReturn($toggleValue === '1');

        $queuedStartNumber = $startNumber + $this->batchSize;
        $queuedDiscs = array_slice($discs, $this->batchSize);
        $discs = array_slice($discs, 0, $this->batchSize);
        $options = [
            'discs' => $queuedDiscs,
            'startNumber' => $queuedStartNumber,
            'type' => $type,
            'user' => $userId
        ];

        $generateAndStoreData = [
            'template' => $expectedTemplate,
            'query' => array_merge($discs, ['user' => $userId]),
            'knownValues' => $this->getKnownValues($bookmark, $startNumber),
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
            'jobName' => $type . ' Disc List',
            'user' => $userId
        ];
        $this->expectedSideEffect(EnqueueFileCommand::class, $printQueueData, new Result());

        $data = [
            'type' => Queue::TYPE_DISC_PRINTING,
            'status' => Queue::STATUS_QUEUED,
            'options' => json_encode($options)
        ];
        $this->expectedSideEffect(CreatQueue::class, $data, new Result());

        $this->repoMap[$repo]
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

    /**
     * The pinned-layout SystemParameter selects the Gotenberg-specific base
     * template; anything else (including a missing row) keeps the legacy one.
     */
    public static function pinnedLayoutProvider(): array
    {
        return [
            'goods, toggle missing' => ['Goods', SystemParameter::GOODS_DISC_PINNED_LAYOUT, 'GoodsDisc', 'Disc_List', null, 'GVDiscTemplate'],
            'goods, toggle off' => ['Goods', SystemParameter::GOODS_DISC_PINNED_LAYOUT, 'GoodsDisc', 'Disc_List', '0', 'GVDiscTemplate'],
            'goods, toggle on' => ['Goods', SystemParameter::GOODS_DISC_PINNED_LAYOUT, 'GoodsDisc', 'Disc_List', '1', 'GVDiscTemplateGotenberg'],
            'psv, toggle missing' => ['PSV', SystemParameter::PSV_DISC_PINNED_LAYOUT, 'PsvDisc', 'Psv_Disc_Page', null, 'PSVDiscTemplate'],
            'psv, toggle off' => ['PSV', SystemParameter::PSV_DISC_PINNED_LAYOUT, 'PsvDisc', 'Psv_Disc_Page', '0', 'PSVDiscTemplate'],
            'psv, toggle on' => ['PSV', SystemParameter::PSV_DISC_PINNED_LAYOUT, 'PsvDisc', 'Psv_Disc_Page', '1', 'PSVDiscTemplateGotenberg'],
        ];
    }

    protected function getDiscs(): mixed
    {
        $discs = [];
        for ($i = 0; $i <= $this->batchSize +  2; $i++) {
            $discs[] = $i + 1;
        }
        return $discs;
    }

    protected function getKnownValues(string $bookmark, mixed $startNumber): mixed
    {
        $knownValues = [
            $bookmark => []
        ];
        $discNumber = (int) $startNumber;
        for ($i = 0; $i < $this->batchSize; $i++) {
            $knownValues[$bookmark][$i]['discNo'] = $discNumber++;
        }
        return $knownValues;
    }
}
