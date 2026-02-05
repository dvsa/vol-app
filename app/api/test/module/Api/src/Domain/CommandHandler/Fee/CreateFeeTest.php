<?php

declare(strict_types=1);

/**
 * Create Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Fee;

use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Fee\CreateFee;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Domain\Repository\Fee;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Transfer\Command\Fee\RecommendWaive as RecommendWaiveCmd;
use Dvsa\Olcs\Transfer\Command\Fee\ApproveWaive as ApproveWaiveCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Create Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateFeeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateFee();
        $this->mockRepo('Fee', Fee::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            FeeEntity::STATUS_OUTSTANDING,
            FeeEntity::STATUS_PAID
        ];

        $this->references = [
            FeeType::class => [
                99 => m::mock(FeeType::class),
                101 => m::mock(FeeType::class)
            ],
            Task::class => [
                11 => m::mock(Task::class)
            ],
            Application::class => [
                22 => m::mock(Application::class)
            ],
            Licence::class => [
                33 => m::mock(Licence::class)
            ],
            BusReg::class => [
                44 => m::mock(BusReg::class)
            ],
            IrfoGvPermit::class => [
                55 => m::mock(IrfoGvPermit::class)
            ],
            IrfoPsvAuth::class => [
                66 => m::mock(IrfoPsvAuth::class)
            ],
            IrhpApplication::class => [
                77 => m::mock(IrhpApplication::class)
            ],
            IrhpPermitApplication::class => [
                88 => m::mock(IrhpPermitApplication::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'feeType' => 99,
            'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
            'amount' => 10.5,
            'quantity' => 2,
            'task' => 11,
            'application' => 22,
            'licence' => 33,
            'busReg' => 44,
            'irhpApplication' => 77,
            'irhpPermitApplication' => 88,
            'invoicedDate' => '2015-01-01',
            'description' => 'Some fee',
        ];

        $this->mapReference(FeeType::class, 99)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(false)
            ->shouldReceive('isAdjustment')
            ->andReturn(false)
            ->shouldReceive('getDescription')
            ->andReturn('some fee type');

        /** @var FeeEntity $savedFee */
        $savedFee = null;

        $command = Cmd::create($data);

        $this->repoMap['Fee']->shouldReceive('save')
            ->once()
            ->with(m::type(FeeEntity::class))
            ->andReturnUsing(
                function (FeeEntity $fee) use (&$savedFee) {
                    $fee->setId(111);
                    $savedFee = $fee;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 111,
            ],
            'messages' => [
                'Fee created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('Some fee', $savedFee->getDescription());
        $this->assertEquals('2015-01-01', $savedFee->getInvoicedDate()->format('Y-m-d'));
        $this->assertSame($this->references[Licence::class][33], $savedFee->getLicence());
        $this->assertSame($this->references[Application::class][22], $savedFee->getApplication());
        $this->assertSame($this->references[BusReg::class][44], $savedFee->getBusReg());
        $this->assertSame($this->references[Task::class][11], $savedFee->getTask());
        $this->assertSame($this->references[IrhpApplication::class][77], $savedFee->getIrhpApplication());
        $this->assertSame($this->references[IrhpPermitApplication::class][88], $savedFee->getIrhpPermitApplication());
        $this->assertEquals(21, $savedFee->getNetAmount());
        $this->assertEquals(21, $savedFee->getGrossAmount());
        $this->assertEquals(0, $savedFee->getVatAmount());
        $this->assertSame($this->refData[FeeEntity::STATUS_OUTSTANDING], $savedFee->getFeeStatus());
        $this->assertSame($this->references[FeeType::class][99], $savedFee->getFeeType());
    }

    public function testHandleCommandForApplicationFee(): void
    {
        $data = [
            'quantity' => 2,
            'feeType' => 99,
            'application' => 22,
            'invoicedDate' => '2015-01-01',
        ];

        $this->mapReference(FeeType::class, 99)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(false)
            ->shouldReceive('isAdjustment')
            ->andReturn(false)
            ->shouldReceive('getDescription')
            ->andReturn('some fee type')
            ->shouldReceive('getAmount')
            ->andReturn(123.45);

        $this->mapReference(Application::class, 22)
            ->setLicence($this->mapReference(Licence::class, 33));

        /** @var FeeEntity $savedFee */
        $savedFee = null;

        $command = Cmd::create($data);

        $this->repoMap['Fee']->shouldReceive('save')
            ->once()
            ->with(m::type(FeeEntity::class))
            ->andReturnUsing(
                function (FeeEntity $fee) use (&$savedFee) {
                    $fee->setId(111);
                    $savedFee = $fee;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 111,
            ],
            'messages' => [
                'Fee created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('2015-01-01', $savedFee->getInvoicedDate()->format('Y-m-d'));
        $this->assertSame($this->references[Licence::class][33], $savedFee->getLicence());
        $this->assertSame($this->references[Application::class][22], $savedFee->getApplication());
        $this->assertEquals(246.9, $savedFee->getNetAmount());
        $this->assertEquals(246.9, $savedFee->getGrossAmount());
        $this->assertEquals(0, $savedFee->getVatAmount());
        $this->assertSame($this->refData[FeeEntity::STATUS_OUTSTANDING], $savedFee->getFeeStatus());
        $this->assertSame($this->references[FeeType::class][99], $savedFee->getFeeType());
    }

    public function testHandleCommandForBusRegFee(): void
    {
        $data = [
            'feeType' => 99,
            'busReg' => 44,
            'invoicedDate' => '2015-01-01',
        ];

        $this->mapReference(FeeType::class, 99)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(false)
            ->shouldReceive('isAdjustment')
            ->andReturn(false)
            ->shouldReceive('getDescription')
            ->andReturn('some fee type')
            ->shouldReceive('getAmount')
            ->andReturn('123.45');

        $this->mapReference(BusReg::class, 44)
            ->setLicence($this->mapReference(Licence::class, 33));

        /** @var FeeEntity $savedFee */
        $savedFee = null;

        $command = Cmd::create($data);

        $this->repoMap['Fee']->shouldReceive('save')
            ->once()
            ->with(m::type(FeeEntity::class))
            ->andReturnUsing(
                function (FeeEntity $fee) use (&$savedFee) {
                    $fee->setId(111);
                    $savedFee = $fee;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 111,
            ],
            'messages' => [
                'Fee created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('some fee type', $savedFee->getDescription());
        $this->assertEquals('2015-01-01', $savedFee->getInvoicedDate()->format('Y-m-d'));
        $this->assertSame($this->references[Licence::class][33], $savedFee->getLicence());
        $this->assertSame($this->references[BusReg::class][44], $savedFee->getBusReg());
        $this->assertEquals('123.45', $savedFee->getNetAmount());
        $this->assertEquals('123.45', $savedFee->getGrossAmount());
        $this->assertEquals(0, $savedFee->getVatAmount());
        $this->assertSame($this->refData[FeeEntity::STATUS_OUTSTANDING], $savedFee->getFeeStatus());
        $this->assertSame($this->references[FeeType::class][99], $savedFee->getFeeType());
    }

    public function testHandleCommandForIrfoGvPermitFee(): void
    {
        $data = [
            'feeType' => 99,
            'irfoGvPermit' => 55,
            'invoicedDate' => '2015-01-01',
            'irfoFeeExempt' => 'Y',
        ];

        $this->mapReference(FeeType::class, 99)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(false)
            ->shouldReceive('isAdjustment')
            ->andReturn(false)
            ->shouldReceive('getDescription')
            ->andReturn('some fee type')
            ->shouldReceive('getAmount')
            ->andReturn('123.45');

        /** @var FeeEntity $savedFee */
        $savedFee = null;

        $command = Cmd::create($data);

        $this->repoMap['Fee']->shouldReceive('save')
            ->once()
            ->with(m::type(FeeEntity::class))
            ->andReturnUsing(
                function (FeeEntity $fee) use (&$savedFee) {
                    $fee->setId(111);
                    $savedFee = $fee;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 111,
            ],
            'messages' => [
                'Fee created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('some fee type', $savedFee->getDescription());
        $this->assertEquals('2015-01-01', $savedFee->getInvoicedDate()->format('Y-m-d'));
        $this->assertSame($this->references[IrfoGvPermit::class][55], $savedFee->getIrfoGvPermit());
        $this->assertEquals('123.45', $savedFee->getNetAmount());
        $this->assertEquals('123.45', $savedFee->getGrossAmount());
        $this->assertEquals(0, $savedFee->getVatAmount());
        $this->assertSame($this->refData[FeeEntity::STATUS_OUTSTANDING], $savedFee->getFeeStatus());
        $this->assertSame($this->references[FeeType::class][99], $savedFee->getFeeType());
        $this->assertEquals('Y', $savedFee->getIrfoFeeExempt());
    }

    public function testHandleCommandForIrfoPsvAuthFee(): void
    {
        $data = [
            'feeType' => 99,
            'irfoPsvAuth' => 66,
            'invoicedDate' => '2015-01-01',
        ];

        $this->mapReference(FeeType::class, 99)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(false)
            ->shouldReceive('isAdjustment')
            ->andReturn(false)
            ->shouldReceive('getDescription')
            ->andReturn('some fee type')
            ->shouldReceive('getAmount')
            ->andReturn('123.45');

        /** @var FeeEntity $savedFee */
        $savedFee = null;

        $command = Cmd::create($data);

        $this->repoMap['Fee']->shouldReceive('save')
            ->once()
            ->with(m::type(FeeEntity::class))
            ->andReturnUsing(
                function (FeeEntity $fee) use (&$savedFee) {
                    $fee->setId(111);
                    $savedFee = $fee;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 111,
            ],
            'messages' => [
                'Fee created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('some fee type', $savedFee->getDescription());
        $this->assertEquals('2015-01-01', $savedFee->getInvoicedDate()->format('Y-m-d'));
        $this->assertSame($this->references[IrfoPsvAuth::class][66], $savedFee->getIrfoPsvAuth());
        $this->assertEquals('123.45', $savedFee->getNetAmount());
        $this->assertEquals('123.45', $savedFee->getGrossAmount());
        $this->assertEquals(0, $savedFee->getVatAmount());
        $this->assertSame($this->refData[FeeEntity::STATUS_OUTSTANDING], $savedFee->getFeeStatus());
        $this->assertSame($this->references[FeeType::class][99], $savedFee->getFeeType());
    }

    public function testHandleCommandForMiscFee(): void
    {
        $data = [
            'feeType' => 101,
            'amount' => '123.45',
            'invoicedDate' => '2015-01-01',
        ];

        $this->mapReference(FeeType::class, 101)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(true)
            ->shouldReceive('getDescription')
            ->andReturn('some fee type');

        /** @var FeeEntity $savedFee */
        $savedFee = null;

        $command = Cmd::create($data);

        $this->repoMap['Fee']->shouldReceive('save')
            ->once()
            ->with(m::type(FeeEntity::class))
            ->andReturnUsing(
                function (FeeEntity $fee) use (&$savedFee) {
                    $fee->setId(111);
                    $savedFee = $fee;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 111,
            ],
            'messages' => [
                'Fee created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('some fee type', $savedFee->getDescription());
        $this->assertEquals('2015-01-01', $savedFee->getInvoicedDate()->format('Y-m-d'));
        $this->assertEquals('123.45', $savedFee->getNetAmount());
        $this->assertEquals('123.45', $savedFee->getGrossAmount());
        $this->assertEquals(0, $savedFee->getVatAmount());
        $this->assertSame($this->refData[FeeEntity::STATUS_OUTSTANDING], $savedFee->getFeeStatus());
        $this->assertSame($this->references[FeeType::class][101], $savedFee->getFeeType());
    }

    public function testValidateMiscFeeType(): void
    {
        $feeType = m::mock(FeeType::class)
            ->shouldReceive('isMiscellaneous')
            ->once()
            ->andReturn(true)
            ->getMock();

        $command = Cmd::create([]);

        $this->assertTrue($this->sut->validate($command, $feeType));
    }

    public function testValidateAdjustmentFeeType(): void
    {
        $feeType = m::mock(FeeType::class)
            ->shouldReceive('isMiscellaneous')
            ->once()
            ->andReturn(false)
            ->shouldReceive('isAdjustment')
            ->once()
            ->andReturn(true)
            ->getMock();

        $command = Cmd::create([]);

        $this->assertTrue($this->sut->validate($command, $feeType));
    }

    public function testValidateNoLinkedEntity(): void
    {
        $feeType = m::mock(FeeType::class)
            ->shouldReceive('isMiscellaneous')
            ->once()
            ->andReturn(false)
            ->shouldReceive('isAdjustment')
            ->once()
            ->andReturn(false)
            ->getMock();

        $command = Cmd::create([]);

        $this->expectException(ValidationException::class);

        $this->sut->validate($command, $feeType);
    }

    public function testValidateIrfoBoth(): void
    {
        $feeType = m::mock(FeeType::class)
            ->shouldReceive('isMiscellaneous')
            ->once()
            ->andReturn(false)
            ->shouldReceive('isAdjustment')
            ->once()
            ->andReturn(false)
            ->getMock();

        $command = Cmd::create(
            [
                'irfoGvPermit' => 1,
                'irfoPsvAuth' => 2,
            ]
        );

        $this->expectException(ValidationException::class);

        $this->sut->validate($command, $feeType);
    }

    /**
     * @param $amount
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('feeAmountsProvider')]
    public function testHandleCommandForZeroAmountFee(mixed $amount, mixed $status): void
    {

        $data = [
            'feeType' => 101,
            'amount' => $amount,
            'invoicedDate' => '2015-01-01',
            'waiveReason' => 'waive reason',
        ];

        $this->mapReference(FeeType::class, 101)
            ->shouldReceive('isMiscellaneous')
            ->andReturn(true)
            ->shouldReceive('getDescription')
            ->andReturn('some fee type');

        /** @var FeeEntity $savedFee */
        $savedFee = null;

        $command = Cmd::create($data);

        $this->repoMap['Fee']->shouldReceive('save')
            ->once()
            ->with(m::type(FeeEntity::class))
            ->andReturnUsing(
                function (FeeEntity $fee) use (&$savedFee) {
                    $fee->setId(111);
                    $savedFee = $fee;
                }
            );

        if ($status === FeeEntity::STATUS_PAID) {
            $waiveData = ['id' => 111, 'version' => 1, 'waiveReason' => 'waive reason'];
            $this->expectedSideEffect(
                RecommendWaiveCmd::class,
                $waiveData,
                (new Result())->addMessage('waive recommended')
            );
            $this->expectedSideEffect(
                ApproveWaiveCmd::class,
                $waiveData,
                (new Result())->addMessage('waive approved')
            );
        }

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'fee' => 111,
            ],
            'messages' => [
                'Fee created successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertEquals('some fee type', $savedFee->getDescription());
        $this->assertEquals('2015-01-01', $savedFee->getInvoicedDate()->format('Y-m-d'));
        $this->assertEquals($amount, $savedFee->getNetAmount());
        $this->assertEquals($amount, $savedFee->getGrossAmount());
        $this->assertEquals(0, $savedFee->getVatAmount());
        $this->assertSame($this->refData[$status], $savedFee->getFeeStatus());
        $this->assertSame($this->references[FeeType::class][101], $savedFee->getFeeType());
    }

    public static function feeAmountsProvider(): array
    {
        return [
            [0, FeeEntity::STATUS_PAID],
            [0.0, FeeEntity::STATUS_PAID],
            [0.00, FeeEntity::STATUS_PAID],
            [0.01, FeeEntity::STATUS_OUTSTANDING],
            [-0.0, FeeEntity::STATUS_PAID],
            ['0', FeeEntity::STATUS_PAID],
            ['0.0', FeeEntity::STATUS_PAID],
            ['0.00', FeeEntity::STATUS_PAID],
            ['0.01', FeeEntity::STATUS_OUTSTANDING],
            [null, FeeEntity::STATUS_PAID],
            [-0.01, FeeEntity::STATUS_OUTSTANDING]
        ];
    }
}
