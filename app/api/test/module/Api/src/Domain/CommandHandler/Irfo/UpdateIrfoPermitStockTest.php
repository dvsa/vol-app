<?php

declare(strict_types=1);

/**
 * Update IrfoPermitStock Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\UpdateIrfoPermitStock as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPermitStock;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as IrfoGvPermitEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock as IrfoPermitStockEntity;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPermitStock as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update IrfoPermitStock Test
 */
class UpdateIrfoPermitStockTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('IrfoPermitStock', IrfoPermitStock::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoPermitStockEntity::STATUS_IN_STOCK
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'ids' => array_fill(0, Sut::MAX_IDS_COUNT, 'id'),
            'status' => IrfoPermitStockEntity::STATUS_IN_STOCK
        ];

        $command = Cmd::create($data);

        /** @var IrfoPermitStockEntity $irfoPermitStock */
        $irfoPermitStock1 = m::mock(IrfoPermitStockEntity::class)->makePartial();
        $irfoPermitStock1->setId(1001);
        $irfoPermitStock1->setSerialNo(101);
        $irfoPermitStock1->setStatus(IrfoPermitStockEntity::STATUS_RETURNED);

        $irfoPermitStock2 = m::mock(IrfoPermitStockEntity::class)->makePartial();
        $irfoPermitStock2->setId(1002);
        $irfoPermitStock2->setSerialNo(103);
        $irfoPermitStock2->setStatus(IrfoPermitStockEntity::STATUS_VOID);
        $irfoPermitStock2->setIrfoGvPermit(m::mock(IrfoGvPermitEntity::class));

        $this->repoMap['IrfoPermitStock']->shouldReceive('fetchByIds')
            ->once()
            ->with($data['ids'])
            ->andReturn(
                [
                    $irfoPermitStock1->getSerialNo() => $irfoPermitStock1,
                    $irfoPermitStock2->getSerialNo() => $irfoPermitStock2
                ]
            );

        $savedIrfoPermitStocks = [];

        $this->repoMap['IrfoPermitStock']->shouldReceive('save')
            ->times(2)
            ->with(m::type(IrfoPermitStockEntity::class))
            ->andReturnUsing(
                function (IrfoPermitStockEntity $irfoPermitStock) use (&$savedIrfoPermitStocks) {
                    $savedIrfoPermitStocks[] = $irfoPermitStock;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'IRFO Permit Stock updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(2, sizeof($savedIrfoPermitStocks));

        foreach ($savedIrfoPermitStocks as $savedIrfoPermitStock) {
            $this->assertSame(
                $this->refData[IrfoPermitStockEntity::STATUS_IN_STOCK],
                $savedIrfoPermitStock->getStatus()
            );
            $this->assertNull(
                $savedIrfoPermitStock->getIrfoGvPermit()
            );
        }
    }

    public function testHandleCommandWithMaxIdsCountExceeded(): void
    {
        $this->expectException(Exception\ValidationException::class);

        $data = [
            'ids' => array_fill(0, Sut::MAX_IDS_COUNT + 1, 'id'),
            'status' => IrfoPermitStockEntity::STATUS_IN_STOCK
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }
}
