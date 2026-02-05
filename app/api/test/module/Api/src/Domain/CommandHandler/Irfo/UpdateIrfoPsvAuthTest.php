<?php

declare(strict_types=1);

/**
 * Update IrfoPsvAuth Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Mockery as m;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Fee\CreateFee as FeeCreateFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\UpdateIrfoPsvAuth;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuth;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuthNumber;
use Dvsa\Olcs\Api\Domain\Repository\FeeType;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\Fee\Fee as FeeEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as IrfoPsvAuthEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthNumber as IrfoPsvAuthNumberEntity;
use Dvsa\Olcs\Transfer\Command\Irfo\UpdateIrfoPsvAuth as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Update IrfoPsvAuth Test
 */
class UpdateIrfoPsvAuthTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UpdateIrfoPsvAuth();
        $this->mockRepo('IrfoPsvAuth', IrfoPsvAuth::class);
        $this->mockRepo('IrfoPsvAuthNumber', IrfoPsvAuthNumber::class);
        $this->mockRepo('FeeType', FeeType::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrfoPsvAuthEntity::STATUS_PENDING,
            IrfoPsvAuthEntity::STATUS_RENEW,
            IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY,
            FeeTypeEntity::FEE_TYPE_IRFOPSVAPP
        ];

        $this->references = [
            IrfoPsvAuthType::class => [
                22 => m::mock(IrfoPsvAuthType::class)
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

    public function testHandleCommand(): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
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
                ['id' => 101, 'name' => 'updated number', 'version' => 1],
                ['name' => 'new number'],
                ['name' => ''],
            ],
        ];

        $command = Cmd::create($data);

        /** @var IrfoPsvAuthNumberEntity $irfoPsvAuthNumber */
        $irfoPsvAuthNumber1 = m::mock(IrfoPsvAuthNumberEntity::class)->makePartial();
        $irfoPsvAuthNumber1->setId(101);
        $irfoPsvAuthNumber1->setName('existing number');

        $irfoPsvAuthNumber2 = m::mock(IrfoPsvAuthNumberEntity::class)->makePartial();
        $irfoPsvAuthNumber2->setId(102);
        $irfoPsvAuthNumber2->setName('deleted number');

        /** @var IrfoPsvAuthEntity $irfoPsvAuth */
        $irfoPsvAuth = m::mock(IrfoPsvAuthEntity::class)->makePartial();
        $irfoPsvAuth->setId(1);
        $irfoPsvAuth->setStatus($this->refData[IrfoPsvAuthEntity::STATUS_PENDING]);
        $irfoPsvAuth->setIrfoPsvAuthNumbers([$irfoPsvAuthNumber1, $irfoPsvAuthNumber2]);

        $this->repoMap['IrfoPsvAuth']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($irfoPsvAuth);

        $this->repoMap['IrfoPsvAuthNumber']->shouldReceive('fetchById')
            ->once()
            ->with(101, Query::HYDRATE_OBJECT, 1)
            ->andReturn($irfoPsvAuthNumber1);

        /** @var IrfoPsvAuthEntity $savedIrfoPsvAuth */
        $savedIrfoPsvAuth = null;

        $this->repoMap['IrfoPsvAuth']->shouldReceive('save')
            ->once()
            ->with(m::type(IrfoPsvAuthEntity::class))
            ->andReturnUsing(
                function (IrfoPsvAuthEntity $irfoPsvAuth) use (&$savedIrfoPsvAuth) {
                    $savedIrfoPsvAuth = $irfoPsvAuth;
                }
            );

        $savedIrfoPsvAuthNumbers = null;
        $deletedIrfoPsvAuthNumbers = null;

        $this->repoMap['IrfoPsvAuthNumber']->shouldReceive('save')
            ->times(2)
            ->with(m::type(IrfoPsvAuthNumberEntity::class))
            ->andReturnUsing(
                function (IrfoPsvAuthNumberEntity $irfoPsvAuthNumber) use (&$savedIrfoPsvAuthNumbers) {
                    $savedIrfoPsvAuthNumbers[] = $irfoPsvAuthNumber;
                }
            );

        $this->repoMap['IrfoPsvAuthNumber']->shouldReceive('delete')
            ->once()
            ->with(m::type(IrfoPsvAuthNumberEntity::class))
            ->andReturnUsing(
                function (IrfoPsvAuthNumberEntity $irfoPsvAuthNumber) use (&$deletedIrfoPsvAuthNumbers) {
                    $deletedIrfoPsvAuthNumbers[] = $irfoPsvAuthNumber;
                }
            );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irfoPsvAuth' => 1,
            ],
            'messages' => [
                'IRFO PSV Auth updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->references[IrfoPsvAuthType::class][$data['irfoPsvAuthType']],
            $savedIrfoPsvAuth->getIrfoPsvAuthType()
        );
        $this->assertSame(
            $this->refData[IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY],
            $savedIrfoPsvAuth->getJourneyFrequency()
        );

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

        $this->assertEquals('updated number', $savedIrfoPsvAuthNumbers[0]->getName());
        $this->assertEquals('new number', $savedIrfoPsvAuthNumbers[1]->getName());
        $this->assertEquals('deleted number', $deletedIrfoPsvAuthNumbers[0]->getName());
    }

    public function testHandleCommandForRenew(): void
    {
        $data = [
            'id' => 1,
            'version' => 1,
            'irfoPsvAuthType' => 22,
            'serviceRouteFrom' => 'From',
            'serviceRouteTo' => 'To',
            'journeyFrequency' => IrfoPsvAuthEntity::JOURNEY_FREQ_DAILY,
            'isFeeExemptApplication' => 'N',
            'irfoPsvAuthNumbers' => [],
        ];

        $command = Cmd::create($data);

        /** @var IrfoPsvAuthEntity $irfoPsvAuth */
        $irfoPsvAuth = m::mock(IrfoPsvAuthEntity::class)->makePartial();
        $irfoPsvAuth->setId(1);
        $irfoPsvAuth->setStatus($this->refData[IrfoPsvAuthEntity::STATUS_RENEW]);
        $irfoPsvAuth->setIrfoPsvAuthNumbers([]);

        $this->repoMap['IrfoPsvAuth']->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, 1)
            ->andReturn($irfoPsvAuth);

        /** @var IrfoPsvAuthEntity $savedIrfoPsvAuth */
        $savedIrfoPsvAuth = null;

        $this->repoMap['IrfoPsvAuth']->shouldReceive('save')
            ->once()
            ->with(m::type(IrfoPsvAuthEntity::class))
            ->andReturnUsing(
                function (IrfoPsvAuthEntity $irfoPsvAuth) use (&$savedIrfoPsvAuth) {
                    $savedIrfoPsvAuth = $irfoPsvAuth;
                }
            );

        $this->repoMap['FeeType']->shouldReceive('getLatestIrfoFeeType')
            ->andReturn($this->references[FeeTypeEntity::class][1]);

        $this->expectedSideEffect(
            FeeCreateFee::class,
            [
                'irfoGvPermit' => null,
                'invoicedDate' => date('Y-m-d'),
                'description' => ' for Auth ' . 1,
                'feeType' => 1,
                'amount' => 0.0,
                'feeStatus' => FeeEntity::STATUS_OUTSTANDING,
                'application' => null,
                'busReg' => null,
                'licence' => null,
                'task' => null,
                'irfoPsvAuth' => 1,
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'irfoPsvAuth' => 1,
            ],
            'messages' => [
                'IRFO PSV Auth updated successfully'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());

        $this->assertSame(
            $this->refData[IrfoPsvAuthEntity::STATUS_PENDING],
            $savedIrfoPsvAuth->getStatus()
        );

        $this->assertInstanceOf(\DateTime::class, $savedIrfoPsvAuth->getRenewalDate());
    }
}
