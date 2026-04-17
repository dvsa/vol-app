<?php

declare(strict_types=1);

/**
 * Create IrfoPermitStock Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\CreateIrfoPermitStock;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPermitStock;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoCountry;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPermitStock as IrfoPermitStockEntity;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoPermitStock as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Create IrfoPermitStock Test
 */
class CreateIrfoPermitStockTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateIrfoPermitStock();
        $this->mockRepo('IrfoPermitStock', IrfoPermitStock::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoPermitStockEntity::STATUS_IN_STOCK
        ];

        $this->references = [
            IrfoCountry::class => [
                11 => m::mock(IrfoCountry::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'irfoCountry' => 11,
            'validForYear' => 2015,
            'status' => IrfoPermitStockEntity::STATUS_IN_STOCK,
            'serialNoStart' => 100,
            'serialNoEnd' => 104
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

        $this->repoMap['IrfoPermitStock']->shouldReceive('fetchUsingSerialNoStartEnd')
            ->once()
            ->with($command)
            ->andReturn(
                [
                    $irfoPermitStock1->getSerialNo() => $irfoPermitStock1,
                    $irfoPermitStock2->getSerialNo() => $irfoPermitStock2
                ]
            );

        $savedIrfoPermitStocks = [];
        $nextSavedId = 1003;

        $this->repoMap['IrfoPermitStock']->shouldReceive('save')
            ->times(5)
            ->with(m::type(IrfoPermitStockEntity::class))
            ->andReturnUsing(
                function (IrfoPermitStockEntity $irfoPermitStock) use (&$savedIrfoPermitStocks, &$nextSavedId) {
                    if (!$irfoPermitStock->getId()) {
                        $irfoPermitStock->setId($nextSavedId);
                        $nextSavedId++;
                    }
                    $savedIrfoPermitStocks[] = $irfoPermitStock;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'IRFO Permit Stock created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals(5, sizeof($savedIrfoPermitStocks));

        $serialNumbersCreated = [];

        foreach ($savedIrfoPermitStocks as $savedIrfoPermitStock) {
            $serialNumbersCreated[] = $savedIrfoPermitStock->getSerialNo();

            $this->assertSame(
                $this->refData[IrfoPermitStockEntity::STATUS_IN_STOCK],
                $savedIrfoPermitStock->getStatus()
            );
        }

        $this->assertEquals(
            [100, 101, 102, 103, 104],
            $serialNumbersCreated
        );
    }

    public function testHandleCommandWithInvalidSerialNoStartEnd(): void
    {
        $this->expectException(Exception\ValidationException::class);

        $data = [
            'serialNoStart' => 100,
            'serialNoEnd' => 99
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }

    public function testHandleCommandWithInvalidSerialNoMaxDiffExceeded(): void
    {
        $this->expectException(Exception\ValidationException::class);

        $serialNoStart = 100;
        $data = [
            'serialNoStart' => $serialNoStart,
            'serialNoEnd' => $serialNoStart + CreateIrfoPermitStock::MAX_DIFF + 1
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }
}
