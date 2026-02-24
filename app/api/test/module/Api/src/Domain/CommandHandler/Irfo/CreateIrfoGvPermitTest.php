<?php

declare(strict_types=1);

/**
 * Create Irfo Gv Permit Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\CreateIrfoGvPermit;
use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoGvPermit as Cmd;
use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermit;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermitType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as IrfoGvPermitEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as FeeCreateFee;
use Dvsa\Olcs\Api\Entity\System\RefData as RefDataEntity;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Create Irfo Gv Permit Test
 */
class CreateIrfoGvPermitTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateIrfoGvPermit();
        $this->mockRepo('IrfoGvPermit', IrfoGvPermit::class);
        $this->mockRepo('FeeType', FeeTypeRepository::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoGvPermitEntity::STATUS_PENDING,
            'fee-type-ref-data',
            'irfo-fee-type-ref-data',
            FeeTypeEntity::FEE_TYPE_IRFOGVPERMIT
        ];

        $this->references = [
            Organisation::class => [
                11 => m::mock(Organisation::class)
            ],
            IrfoGvPermitType::class => [
                22 => m::mock(IrfoGvPermitType::class)
            ],
            FeeTypeEntity::class => [
                1 => m::mock(FeeTypeEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'organisation' => 11,
            'irfoGvPermitType' => 22,
            'yearRequired' => 2014,
            'inForceDate' => '2015-01-01',
            'isFeeExempt' => 'N',
            'noOfCopies' => 2,
        ];

        $command = Cmd::create($data);

        $savedIrfoGvPermit = null;

        $this->repoMap['IrfoGvPermit']->shouldReceive('save')
            ->once()
            ->with(m::type(IrfoGvPermitEntity::class))
            ->andReturnUsing(
                function (IrfoGvPermitEntity $irfoGvPermit) use (&$savedIrfoGvPermit) {
                    $irfoGvPermit->setId(111);
                    $savedIrfoGvPermit = $irfoGvPermit;
                }
            );

        $this->repoMap['IrfoGvPermit']->shouldReceive('getRefDataReference')
            ->with(FeeTypeEntity::FEE_TYPE_IRFOGVPERMIT)
            ->andReturn($this->refData['fee-type-ref-data']);

        $this->references[FeeTypeEntity::class][1]->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn(1)
            ->shouldReceive('getFixedValue')
            ->withNoArgs()
            ->andReturn(12.34);

        $this->references[IrfoGvPermitType::class][22]->shouldReceive('getIrfoFeeType')
            ->withNoArgs()
            ->andReturn($this->refData['fee-type-ref-data']);

        $this->repoMap['FeeType']->shouldReceive('getLatestIrfoFeeType')
            ->andReturn($this->references[FeeTypeEntity::class][1]);

        $result1 = new Result();
        $result1->addMessage('IRFO GV Permit side affect');
        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoGvPermit' => 111,
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for IRFO permit ' . 111,
                'feeType' => 1,
                'amount' => 24.68,
                'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                'application' => null,
                'busReg' => null,
                'licence' => null,
                'task' => null
            ],
            $result1
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irfoGvPermit' => 111,
            ],
            'messages' => [
                'IRFO GV Permit created successfully',
                'IRFO GV Permit side affect'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->references[Organisation::class][11], $savedIrfoGvPermit->getOrganisation());
        $this->assertSame($this->references[IrfoGvPermitType::class][22], $savedIrfoGvPermit->getIrfoGvPermitType());
        $this->assertSame(
            $this->refData[IrfoGvPermitEntity::STATUS_PENDING],
            $savedIrfoGvPermit->getIrfoPermitStatus()
        );
        $this->assertEquals(2014, $savedIrfoGvPermit->getYearRequired());
        $this->assertEquals('2015-01-01', $savedIrfoGvPermit->getInForceDate()->format('Y-m-d'));
        $this->assertEquals('N', $savedIrfoGvPermit->getIsFeeExempt());
        $this->assertEquals(2, $savedIrfoGvPermit->getNoOfCopies());
    }

    public function testHandleCommandExemptFee(): void
    {
        $data = [
            'organisation' => 11,
            'irfoGvPermitType' => 22,
            'yearRequired' => 2014,
            'inForceDate' => '2015-01-01',
            'isFeeExempt' => 'Y',
            'noOfCopies' => 1
        ];

        $command = Cmd::create($data);

        $savedIrfoGvPermit = null;

        $this->repoMap['IrfoGvPermit']->shouldReceive('save')
            ->once()
            ->with(m::type(IrfoGvPermitEntity::class))
            ->andReturnUsing(
                function (IrfoGvPermitEntity $irfoGvPermit) use (&$savedIrfoGvPermit) {
                    $irfoGvPermit->setId(111);
                    $savedIrfoGvPermit = $irfoGvPermit;
                }
            );

        $this->repoMap['IrfoGvPermit']->shouldReceive('getRefDataReference')
            ->with(FeeTypeEntity::FEE_TYPE_IRFOGVPERMIT)
            ->andReturn($this->refData['fee-type-ref-data']);

        $this->references[FeeTypeEntity::class][1]->shouldReceive('getId')
            ->andReturn(1);

        $this->references[IrfoGvPermitType::class][22]->shouldReceive('getIrfoFeeType')
            ->with()
            ->andReturn($this->refData['fee-type-ref-data']);

        $this->repoMap['FeeType']->shouldReceive('getLatestIrfoFeeType')
            ->andReturn($this->references[FeeTypeEntity::class][1]);

        $result1 = new Result();
        $result1->addMessage('IRFO GV Permit side affect');
        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoGvPermit' => 111,
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for IRFO permit ' . 111,
                'feeType' => 1,
                'amount' => 0,
                'feeStatus' => FeeEntity::STATUS_PAID,
                'application' => null,
                'busReg' => null,
                'licence' => null,
                'task' => null
            ],
            $result1
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irfoGvPermit' => 111,
            ],
            'messages' => [
                'IRFO GV Permit created successfully',
                'IRFO GV Permit side affect'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame($this->references[Organisation::class][11], $savedIrfoGvPermit->getOrganisation());
        $this->assertSame($this->references[IrfoGvPermitType::class][22], $savedIrfoGvPermit->getIrfoGvPermitType());
        $this->assertSame(
            $this->refData[IrfoGvPermitEntity::STATUS_PENDING],
            $savedIrfoGvPermit->getIrfoPermitStatus()
        );
        $this->assertEquals(2014, $savedIrfoGvPermit->getYearRequired());
        $this->assertEquals('2015-01-01', $savedIrfoGvPermit->getInForceDate()->format('Y-m-d'));
        $this->assertEquals('Y', $savedIrfoGvPermit->getIsFeeExempt());
        $this->assertEquals(1, $savedIrfoGvPermit->getNoOfCopies());
    }

    public function testGetIrfoFeeId(): void
    {
        $organisation = m::mock(Organisation::class);
        $organisation->shouldReceive('getId')
            ->once()
            ->withNoArgs()
            ->andReturn('TEST12');

        $this->assertEquals('IR0TEST12', $this->sut->getIrfoFeeId($organisation));
    }
}
