<?php

declare(strict_types=1);

/**
 * Grant IrfoPsvAuth Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Exception\BadRequestException;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\GrantIrfoPsvAuth;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuth;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuthNumber;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as IrfoPsvAuthEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Transfer\Command\Irfo\GrantIrfoPsvAuth as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Domain\Repository\Fee as FeeRepository;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as FeeCreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Grant IrfoPsvAuth Test
 */
class GrantIrfoPsvAuthTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GrantIrfoPsvAuth();
        $this->mockRepo('IrfoPsvAuth', IrfoPsvAuth::class);
        $this->mockRepo('IrfoPsvAuthNumber', IrfoPsvAuthNumber::class);
        $this->mockRepo('FeeType', FeeTypeRepository::class);
        $this->mockRepo('Fee', FeeRepository::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoPsvAuthEntity::STATUS_PENDING,
            IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY,
            IrfoPsvAuthEntity::STATUS_GRANTED,
            'fee-type-ref-data',
            'irfo-fee-type-ref-data',
            FeeTypeEntity::FEE_TYPE_IRFOPSVAPP,
            FeeTypeEntity::FEE_TYPE_IRFOPSVANN,
            FeeTypeEntity::FEE_TYPE_IRFOPSVCOPY,
            FeeEntity::STATUS_PAID,
            FeeEntity::STATUS_OUTSTANDING,
        ];

        $this->references = [
            Organisation::class => [
                11 => m::mock(Organisation::class)
            ],
            Country::class => [
                'GB' => m::mock(Country::class)
            ],
            FeeTypeEntity::class => [
                1 => m::mock(FeeTypeEntity::class)->makePartial()->setFixedValue(20)
            ],
            IrfoPsvAuthType::class => [
                22 => m::mock(IrfoPsvAuthType::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandAnnualFeeExempt(): void
    {
        $data = [
            'id' => 42,
            'version' => 2,
            'organisation' => 11,
            'irfoPsvAuthType' => 22,
            'status' => IrfoPsvAuthEntity::STATUS_PENDING,
            'validityPeriod' => 1,
            'inForceDate' => '2015-01-01',
            'expiryDate' => '2016-01-01',
            'applicationSentDate' => '2014-01-01',
            'serviceRouteFrom' => 'From',
            'serviceRouteTo' => 'To',
            'journeyFrequency' => IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY,
            'isFeeExemptApplication' => 'Y',
            'isFeeExemptAnnual' => 'Y',
            'exemptionDetails' => 'testing',
            'copiesRequired' => 1,
            'copiesRequiredTotal' => 1,
            'countrys' => ['GB'],
            'irfoPsvAuthNumbers' => [],
        ];

        $command = Cmd::create($data);

        $this->repoMap['FeeType']->shouldReceive('getLatestIrfoFeeType')
            ->andReturn($this->references[FeeTypeEntity::class][1]);

        $irfoPsvAuth = $this->generatePsvAuth($data);

        $feeType = new FeeTypeEntity();
        $fee = new FeeEntity($feeType, 0, $this->refData[FeeEntity::STATUS_PAID]);

        $this->repoMap['IrfoPsvAuth']->shouldReceive('fetchUsingId')
            ->andReturn($irfoPsvAuth)
            ->shouldReceive('save');

        $this->repoMap['Fee']->shouldReceive('fetchApplicationFeeByPsvAuthId')
            ->andReturn($fee);

        $result1 = new Result();
        $result1->addMessage('IRFO PSV Auth Annual Fee created');
        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoGvPermit' => null,
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for Auth ' . $data['id'],
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

        $result2 = new Result();
        $result2->addMessage('IRFO PSV Auth Copies Fee created');
        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoPsvAuth' => $data['id'],
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for Auth ' . $data['id'],
                'feeType' => 1,
                'amount' => 20,
                'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                'application' => null,
                'busReg' => null,
                'licence' => null,
                'task' => null
            ],
            $result2
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irfoPsvAuth' => $data['id'],
            ],
            'messages' => [
                'IRFO PSV Auth granted successfully',
                'IRFO PSV Auth Annual Fee created',
                'IRFO PSV Auth Copies Fee created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests granting whilst creating annual fee that is NOT exempt
     */
    public function testHandleCommandAnnualFeeNotExempt(): void
    {
        $data = [
            'id' => 42,
            'version' => 2,
            'organisation' => 11,
            'irfoPsvAuthType' => 22,
            'status' => IrfoPsvAuthEntity::STATUS_PENDING,
            'validityPeriod' => 4,
            'inForceDate' => '2015-01-01',
            'expiryDate' => '2016-01-01',
            'applicationSentDate' => '2014-01-01',
            'serviceRouteFrom' => 'From',
            'serviceRouteTo' => 'To',
            'journeyFrequency' => IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY,
            'isFeeExemptApplication' => 'N',
            'isFeeExemptAnnual' => 'N',
            'exemptionDetails' => 'testing',
            'copiesRequired' => 1,
            'copiesRequiredTotal' => 1,
            'countrys' => ['GB'],
            'irfoPsvAuthNumbers' => [],
        ];

        $command = Cmd::create($data);

        $this->repoMap['FeeType']->shouldReceive('getLatestIrfoFeeType')
            ->andReturn($this->references[FeeTypeEntity::class][1]);

        $irfoPsvAuth = $this->generatePsvAuth($data);

        $feeType = new FeeTypeEntity();

        $fee = new FeeEntity($feeType, 100, $this->refData[FeeEntity::STATUS_PAID]);

        $this->repoMap['IrfoPsvAuth']->shouldReceive('fetchUsingId')
            ->andReturn($irfoPsvAuth)
            ->shouldReceive('save');

        $this->repoMap['Fee']->shouldReceive('fetchApplicationFeeByPsvAuthId')
            ->andReturn($fee);

        $result1 = new Result();
        $result1->addMessage('IRFO PSV Auth Annual Fee created');
        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoGvPermit' => null,
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for Auth ' . $data['id'],
                'feeType' => 1,
                'amount' => 80,
                'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                'application' => null,
                'busReg' => null,
                'licence' => null,
                'task' => null
            ],
            $result1
        );

        $result2 = new Result();
        $result2->addMessage('IRFO PSV Auth Copies Fee created');
        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoPsvAuth' => $data['id'],
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for Auth ' . $data['id'],
                'feeType' => 1,
                'amount' => 20,
                'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                'application' => null,
                'busReg' => null,
                'licence' => null,
                'task' => null
            ],
            $result2
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irfoPsvAuth' => $data['id'],
            ],
            'messages' => [
                'IRFO PSV Auth granted successfully',
                'IRFO PSV Auth Annual Fee created',
                'IRFO PSV Auth Copies Fee created'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests attempt to grant when application fee still outstanding
     */
    public function testHandleCommandAnnualFeeOutstanding(): void
    {
        $this->expectException(BadRequestException::class);

        $data = [
            'id' => 42,
            'version' => 2,
            'organisation' => 11,
            'irfoPsvAuthType' => 22,
            'status' => IrfoPsvAuthEntity::STATUS_PENDING,
            'validityPeriod' => 1,
            'inForceDate' => '2015-01-01',
            'expiryDate' => '2016-01-01',
            'applicationSentDate' => '2014-01-01',
            'serviceRouteFrom' => 'From',
            'serviceRouteTo' => 'To',
            'journeyFrequency' => IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY,
            'isFeeExemptApplication' => 'Y',
            'isFeeExemptAnnual' => 'Y',
            'exemptionDetails' => 'testing',
            'copiesRequired' => 1,
            'copiesRequiredTotal' => 1,
            'countrys' => ['GB'],
            'irfoPsvAuthNumbers' => [],
        ];

        $command = Cmd::create($data);

        $this->repoMap['FeeType']->shouldReceive('getLatestIrfoFeeType')
            ->andReturn($this->references[FeeTypeEntity::class][1]);

        $irfoPsvAuth = $this->generatePsvAuth($data);

        $feeType = new FeeTypeEntity();
        $fee = new FeeEntity($feeType, 0, $this->refData[FeeEntity::STATUS_OUTSTANDING]);

        $this->repoMap['IrfoPsvAuth']->shouldReceive('fetchUsingId')
            ->andReturn($irfoPsvAuth)
            ->shouldReceive('save');

        $this->repoMap['Fee']->shouldReceive('fetchApplicationFeeByPsvAuthId')
            ->andReturn($fee);

        $this->sut->handleCommand($command);
    }

    private function generatePsvAuth(mixed $data): mixed
    {
        /** @var IrfoPsvAuthEntity $irfoPsvAuth */
        $irfoPsvAuth = m::mock(IrfoPsvAuthEntity::class)->makePartial();
        $irfoPsvAuth->setId($data['id']);
        $irfoPsvAuth->setIrfoPsvAuthNumbers([]);
        $irfoPsvAuth->setIsFeeExemptAnnual($data['isFeeExemptAnnual']);
        $irfoPsvAuth->setValidityPeriod(2);

        $irfoPsvAuth->setStatus($this->refData[IrfoPsvAuthEntity::STATUS_PENDING]);

        return $irfoPsvAuth;
    }
}
