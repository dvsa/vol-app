<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpPermitStock;

use Dvsa\Olcs\Api\Domain\Command\IrhpPermitSector\Create as CreateSectorQuotasCmd;
use Dvsa\Olcs\Api\Domain\Command\IrhpPermitJurisdiction\Create as CreateJurisdictionQuotasCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ValidationException;
use Dvsa\Olcs\Api\Entity\ContactDetails\Country;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermitStock\Create as CreateHandler;
use Dvsa\Olcs\Api\Domain\Repository\Country as CountryRepo;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitStock as PermitStockRepo;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Transfer\Command\IrhpPermitStock\Create as CreateCmd;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as PermitStockEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType;

/**
 * Create IRHP Permit Type Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */
class CreateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateHandler();
        $this->mockRepo('Country', CountryRepo::class);
        $this->mockRepo('IrhpPermitStock', PermitStockRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            PermitStockEntity::STATUS_SCORING_NEVER_RUN,
            RefData::PERMIT_CAT_HORS_CONTINGENT,
        ];
        $this->references = [
            Country::class => [
                Country::ID_MOROCCO => m::mock(Country::class),
            ],
            IrhpPermitType::class => [
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM => m::mock(IrhpPermitType::class),
                IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL => m::mock(IrhpPermitType::class),
            ],
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $cmdData = [
            'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM,
            'validFrom' => '2119-01-01',
            'validTo' => '2119-02-01',
            'initialStock' => '1500',
            'applicationPath' => 2,
            'businessProcess' => 'app_business_process_apg',
            'periodNameKey' => 'period.name.translation.key'
        ];

        $command = CreateCmd::create($cmdData);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PermitStockEntity::class))
            ->andReturnUsing(
                function (PermitStockEntity $permitStock) {
                    $permitStock->setId(1);
                }
            );

        $this->expectedSideEffect(CreateSectorQuotasCmd::class, ['id' => 1], new Result());
        $this->expectedSideEffect(CreateJurisdictionQuotasCmd::class, ['id' => 1], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['IrhpPermitStock' => 1],
            'messages' => ["IRHP Permit Stock '1' created"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandBilateral(): void
    {
        $cmdData = [
            'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_BILATERAL,
            'country' => Country::ID_MOROCCO,
            'permitCategory' => RefData::PERMIT_CAT_HORS_CONTINGENT,
            'validFrom' => '2119-01-01',
            'validTo' => '2119-02-01',
            'initialStock' => '1500',
            'applicationPath' => 2,
            'businessProcess' => 'app_business_process_apg',
            'periodNameKey' => 'period.name.translation.key'
        ];

        $command = CreateCmd::create($cmdData);

        $this->repoMap['IrhpPermitStock']
            ->shouldReceive('save')
            ->once()
            ->with(m::type(PermitStockEntity::class))
            ->andReturnUsing(
                function (PermitStockEntity $permitStock) {
                    $permitStock->setId(1);

                    $this->assertEquals(
                        $this->references[Country::class][Country::ID_MOROCCO],
                        $permitStock->getCountry()
                    );
                    $this->assertEquals(
                        $this->refData[RefData::PERMIT_CAT_HORS_CONTINGENT],
                        $permitStock->getPermitCategory()
                    );
                }
            );

        $this->expectedSideEffect(CreateSectorQuotasCmd::class, ['id' => 1], new Result());
        $this->expectedSideEffect(CreateJurisdictionQuotasCmd::class, ['id' => 1], new Result());

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => ['IrhpPermitStock' => 1],
            'messages' => ["IRHP Permit Stock '1' created"]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    /**
     * Tests method on IrhpPermitStockTrait
     */
    public function testGoodValidityPeriodValidationEcmt(): void
    {
        $cmdData = [
            'initialStock' => 1000,
            'validFrom' => '2100-01-01',
            'validTo' => '2100-12-31',
            'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
        ];

        $cmd = CreateCmd::create($cmdData);
        $this->assertNull($this->sut->validityPeriodValidation($cmd));
    }

    /**
     * Tests method on IrhpPermitStockTrait
     */
    public function testGoodValidityPeriodValidationRemovals(): void
    {
        $cmdData = [
            'initialStock' => 1000,
            'validFrom' => null,
            'validTo' => null,
            'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL,
        ];

        $cmd = CreateCmd::create($cmdData);
        $this->assertNull($this->sut->validityPeriodValidation($cmd));
    }

    /**
     * Tests method on IrhpPermitStockTrait
     */
    public function testBadValidityPeriodValidationEcmt(): void
    {
        $cmdData = [
            'initialStock' => 1000,
            'validFrom' => null,
            'validTo' => null,
            'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
        ];

        $cmd = CreateCmd::create($cmdData);
        $this->expectException(ValidationException::class);
        $this->sut->validityPeriodValidation($cmd);
    }

    /**
     * Tests method on IrhpPermitStockTrait
     */
    public function testToBeforeFromValidityPeriodValidationEcmt(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validity Period End Date must be equal to or later than Validity Period Start Date');

        $cmdData = [
            'initialStock' => 1000,
            'validFrom' => '2100-01-01',
            'validTo' => '2000-12-31',
            'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
        ];

        $cmd = CreateCmd::create($cmdData);
        $this->sut->validityPeriodValidation($cmd);
    }

    public function testValidityPeriodEndDateInPast(): void
    {
        $this->expectException(ValidationException::class);
        $this->expectExceptionMessage('Validity Period End date should be today or in the future');

        $cmdData = [
            'initialStock' => 1000,
            'validFrom' => '2000-01-01',
            'validTo' => '2000-12-31',
            'irhpPermitType' => IrhpPermitType::IRHP_PERMIT_TYPE_ID_ECMT,
        ];

        $cmd = CreateCmd::create($cmdData);
        $this->sut->validityPeriodValidation($cmd);
    }
}
