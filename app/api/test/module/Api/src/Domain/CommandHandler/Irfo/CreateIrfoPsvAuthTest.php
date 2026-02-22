<?php

declare(strict_types=1);

/**
 * Create IrfoPsvAuth Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\CreateIrfoPsvAuth;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuth;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuthNumber;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as IrfoPsvAuthEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthNumber as IrfoPsvAuthNumberEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Transfer\Command\Irfo\CreateIrfoPsvAuth as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as FeeCreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;

/**
 * Create IrfoPsvAuth Test
 */
class CreateIrfoPsvAuthTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateIrfoPsvAuth();
        $this->mockRepo('IrfoPsvAuth', IrfoPsvAuth::class);
        $this->mockRepo('IrfoPsvAuthNumber', IrfoPsvAuthNumber::class);
        $this->mockRepo('FeeType', FeeTypeRepository::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoPsvAuthEntity::STATUS_PENDING,
            IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY,
            'fee-type-ref-data',
            'irfo-fee-type-ref-data',
            FeeTypeEntity::FEE_TYPE_IRFOPSVAPP
        ];

        $this->references = [
            Organisation::class => [
                11 => m::mock(Organisation::class)
            ],
            IrfoPsvAuthType::class => [
                22 => m::mock(IrfoPsvAuthType::class)
                ->shouldReceive('getSectionCode')
                ->once()
                ->andReturn('SC')
                ->getMock()
            ],
            Country::class => [
                'GB' => m::mock(Country::class)
            ],
            FeeTypeEntity::class => [
                1 => m::mock(FeeTypeEntity::class)
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommandApplicationFeeExempt(): void
    {
        $data = [
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
            'irfoPsvAuthNumbers' => [
                ['name' => 'test 1'],
                ['name' => ''],
            ],
        ];

        $command = Cmd::create($data);

        /** @var IrfoPsvAuthEntity $savedIrfoPsvAuth */
        $savedIrfoPsvAuth = null;

        $this->repoMap['IrfoPsvAuth']->shouldReceive('save')
            ->times(2)
            ->with(m::type(IrfoPsvAuthEntity::class))
            ->andReturnUsing(
                function (IrfoPsvAuthEntity $irfoPsvAuth) use (&$savedIrfoPsvAuth) {
                    $irfoPsvAuth->setId(111);
                    $savedIrfoPsvAuth = $irfoPsvAuth;
                }
            );

        /** @var IrfoPsvAuthNumberEntity $savedIrfoPsvAuthNumber */
        $savedIrfoPsvAuthNumber = null;

        $this->repoMap['IrfoPsvAuthNumber']->shouldReceive('save')
            ->once()
            ->with(m::type(IrfoPsvAuthNumberEntity::class))
            ->andReturnUsing(
                function (IrfoPsvAuthNumberEntity $irfoPsvAuthNumber) use (&$savedIrfoPsvAuthNumber) {
                    $irfoPsvAuthNumber->setId(111);
                    $savedIrfoPsvAuthNumber = $irfoPsvAuthNumber;
                }
            );

        $this->repoMap['IrfoPsvAuth']->shouldReceive('getRefDataReference')
            ->with(FeeTypeEntity::FEE_TYPE_IRFOPSVAPP)
            ->andReturn($this->refData['fee-type-ref-data']);

        $this->references[FeeTypeEntity::class][1]->shouldReceive('getId')
            ->andReturn(1);

        $this->references[IrfoPsvAuthType::class][22]->shouldReceive('getIrfoFeeType')
            ->with()
            ->andReturn($this->refData['fee-type-ref-data']);

        $this->repoMap['FeeType']->shouldReceive('getLatestIrfoFeeType')
            ->andReturn($this->references[FeeTypeEntity::class][1]);

        $result1 = new Result();
        $result1->addMessage('IRFO PSV Auth side effect');
        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoGvPermit' => null,
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for Auth ' . 111,
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
                'irfoPsvAuth' => 111,
            ],
            'messages' => [
                'IRFO PSV Auth created successfully',
                'IRFO PSV Auth side effect'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->references[Organisation::class][$data['organisation']],
            $savedIrfoPsvAuth->getOrganisation()
        );
        $this->assertSame(
            $this->references[IrfoPsvAuthType::class][$data['irfoPsvAuthType']],
            $savedIrfoPsvAuth->getIrfoPsvAuthType()
        );
        $this->assertSame(
            $this->refData[IrfoPsvAuthEntity::STATUS_PENDING],
            $savedIrfoPsvAuth->getStatus()
        );
        $this->assertSame(
            $this->refData[IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY],
            $savedIrfoPsvAuth->getJourneyFrequency()
        );

        $this->assertEquals('SC/111', $savedIrfoPsvAuth->getIrfoFileNo());

        $this->assertEquals($data['validityPeriod'], $savedIrfoPsvAuth->getValidityPeriod());
        $this->assertEquals($data['inForceDate'], $savedIrfoPsvAuth->getInForceDate()->format('Y-m-d'));
        $this->assertEquals($data['expiryDate'], $savedIrfoPsvAuth->getExpiryDate()->format('Y-m-d'));
        $this->assertEquals($data['applicationSentDate'], $savedIrfoPsvAuth->getApplicationSentDate()->format('Y-m-d'));
        $this->assertEquals($data['serviceRouteFrom'], $savedIrfoPsvAuth->getServiceRouteFrom());
        $this->assertEquals($data['serviceRouteTo'], $savedIrfoPsvAuth->getServiceRouteTo());
        $this->assertEquals($data['isFeeExemptApplication'], $savedIrfoPsvAuth->getIsFeeExemptApplication());
        $this->assertEquals($data['isFeeExemptAnnual'], $savedIrfoPsvAuth->getIsFeeExemptAnnual());
        $this->assertEquals($data['exemptionDetails'], $savedIrfoPsvAuth->getExemptionDetails());
        $this->assertEquals($data['copiesRequired'], $savedIrfoPsvAuth->getCopiesRequired());
        $this->assertEquals($data['copiesRequiredTotal'], $savedIrfoPsvAuth->getCopiesRequiredTotal());

        $this->assertSame(
            [$this->references[Country::class][$data['countrys'][0]]],
            $savedIrfoPsvAuth->getCountrys()
        );

        $this->assertEquals($data['irfoPsvAuthNumbers'][0]['name'], $savedIrfoPsvAuthNumber->getName());
    }

    public function testHandleCommandApplicationFeeApplied(): void
    {
        $data = [
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
            'isFeeExemptApplication' => 'N',
            'isFeeExemptAnnual' => 'N',
            'exemptionDetails' => '',
            'copiesRequired' => 1,
            'copiesRequiredTotal' => 1,
            'countrys' => ['GB'],
            'irfoPsvAuthNumbers' => [
                ['name' => 'test 1'],
                ['name' => ''],
            ],
        ];

        $command = Cmd::create($data);

        /** @var IrfoPsvAuthEntity $savedIrfoPsvAuth */
        $savedIrfoPsvAuth = null;

        $this->repoMap['IrfoPsvAuth']->shouldReceive('save')
            ->times(2)
            ->with(m::type(IrfoPsvAuthEntity::class))
            ->andReturnUsing(
                function (IrfoPsvAuthEntity $irfoPsvAuth) use (&$savedIrfoPsvAuth) {
                    $irfoPsvAuth->setId(111);
                    $savedIrfoPsvAuth = $irfoPsvAuth;
                }
            );

        /** @var IrfoPsvAuthNumberEntity $savedIrfoPsvAuthNumber */
        $savedIrfoPsvAuthNumber = null;

        $this->repoMap['IrfoPsvAuthNumber']->shouldReceive('save')
            ->once()
            ->with(m::type(IrfoPsvAuthNumberEntity::class))
            ->andReturnUsing(
                function (IrfoPsvAuthNumberEntity $irfoPsvAuthNumber) use (&$savedIrfoPsvAuthNumber) {
                    $irfoPsvAuthNumber->setId(111);
                    $savedIrfoPsvAuthNumber = $irfoPsvAuthNumber;
                }
            );

        $this->repoMap['IrfoPsvAuth']->shouldReceive('getRefDataReference')
            ->with(FeeTypeEntity::FEE_TYPE_IRFOPSVAPP)
            ->andReturn($this->refData['fee-type-ref-data']);

        $this->references[FeeTypeEntity::class][1]->shouldReceive('getId')
            ->andReturn(1);

        $this->references[IrfoPsvAuthType::class][22]->shouldReceive('getIrfoFeeType')
            ->with()
            ->andReturn($this->refData['fee-type-ref-data']);

        $this->repoMap['FeeType']->shouldReceive('getLatestIrfoFeeType')
            ->andReturn($this->references[FeeTypeEntity::class][1]);

        $result1 = new Result();
        $result1->addMessage('IRFO PSV Auth side effect');
        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoGvPermit' => null,
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for Auth ' . 111,
                'feeType' => 1,
                'amount' => 0.0,
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
                'irfoPsvAuth' => 111,
            ],
            'messages' => [
                'IRFO PSV Auth created successfully',
                'IRFO PSV Auth side effect'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->references[Organisation::class][$data['organisation']],
            $savedIrfoPsvAuth->getOrganisation()
        );
        $this->assertSame(
            $this->references[IrfoPsvAuthType::class][$data['irfoPsvAuthType']],
            $savedIrfoPsvAuth->getIrfoPsvAuthType()
        );
        $this->assertSame(
            $this->refData[IrfoPsvAuthEntity::STATUS_PENDING],
            $savedIrfoPsvAuth->getStatus()
        );
        $this->assertSame(
            $this->refData[IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY],
            $savedIrfoPsvAuth->getJourneyFrequency()
        );

        $this->assertEquals('SC/111', $savedIrfoPsvAuth->getIrfoFileNo());

        $this->assertEquals($data['validityPeriod'], $savedIrfoPsvAuth->getValidityPeriod());
        $this->assertEquals($data['inForceDate'], $savedIrfoPsvAuth->getInForceDate()->format('Y-m-d'));
        $this->assertEquals($data['expiryDate'], $savedIrfoPsvAuth->getExpiryDate()->format('Y-m-d'));
        $this->assertEquals($data['applicationSentDate'], $savedIrfoPsvAuth->getApplicationSentDate()->format('Y-m-d'));
        $this->assertEquals($data['serviceRouteFrom'], $savedIrfoPsvAuth->getServiceRouteFrom());
        $this->assertEquals($data['serviceRouteTo'], $savedIrfoPsvAuth->getServiceRouteTo());
        $this->assertEquals($data['isFeeExemptApplication'], $savedIrfoPsvAuth->getIsFeeExemptApplication());
        $this->assertEquals($data['isFeeExemptAnnual'], $savedIrfoPsvAuth->getIsFeeExemptAnnual());
        $this->assertEquals($data['exemptionDetails'], $savedIrfoPsvAuth->getExemptionDetails());
        $this->assertEquals($data['copiesRequired'], $savedIrfoPsvAuth->getCopiesRequired());
        $this->assertEquals($data['copiesRequiredTotal'], $savedIrfoPsvAuth->getCopiesRequiredTotal());

        $this->assertSame(
            [$this->references[Country::class][$data['countrys'][0]]],
            $savedIrfoPsvAuth->getCountrys()
        );

        $this->assertEquals($data['irfoPsvAuthNumbers'][0]['name'], $savedIrfoPsvAuthNumber->getName());
    }
}
