<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\FeeType as FeeTypeRepository;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock as IrhpPermitStockEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockAvailabilityCounter;
use Dvsa\Olcs\Api\Service\Permits\Availability\StockLicenceMaxPermittedCounter;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\EmissionsCategoryConditionalAdder;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermits;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsFactory;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsGenerator;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\ElementGeneratorContext;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsGeneratorTest extends MockeryTestCase
{
    public const ISSUE_FEE_PER_PERMIT = '5.00';

    #[\PHPUnit\Framework\Attributes\DataProvider('dpGenerate')]
    public function testGenerate(
        mixed $maxPermitted,
        mixed $permitsRemaining,
        mixed $expectedMaxCanApplyFor,
        mixed $isApsg,
        mixed $isUnderConsideration,
        mixed $expectedSkipAvailabilityValidation,
        mixed $isOngoing,
        mixed $expectedIssueFeePerPermit
    ): void {
        $stockId = 22;
        $validityYear = 2015;
        $requiredEuro5 = 13;
        $requiredEuro6 = 7;
        $applicationFeePerPermit = '15.00';

        $irhpApplication = m::mock(IrhpApplicationEntity::class);

        $elementGeneratorContext = m::mock(ElementGeneratorContext::class);
        $elementGeneratorContext->shouldReceive('getQaEntity')
            ->andReturn($irhpApplication);

        $irhpPermitStock = m::mock(IrhpPermitStockEntity::class);
        $irhpPermitStock->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($stockId);
        $irhpPermitStock->shouldReceive('getValidityYear')
            ->withNoArgs()
            ->andReturn($validityYear);

        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication->shouldReceive('getIrhpPermitWindow->getIrhpPermitStock')
            ->withNoArgs()
            ->andReturn($irhpPermitStock);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->withNoArgs()
            ->andReturn($requiredEuro5);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->withNoArgs()
            ->andReturn($requiredEuro6);

        $licence = m::mock(LicenceEntity::class);

        $irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);
        $irhpApplication->shouldReceive('getApplicationFeeProductReference')
            ->withNoArgs()
            ->andReturn(FeeTypeEntity::FEE_TYPE_ECMT_APP_PRODUCT_REF);
        $irhpApplication->shouldReceive('getIssueFeeProductReference')
            ->withNoArgs()
            ->andReturn(FeeTypeEntity::FEE_TYPE_ECMT_SHORT_TERM_ISSUE_PRODUCT_REF);
        $irhpApplication->shouldReceive('getLicence')
            ->withNoArgs()
            ->andReturn($licence);
        $irhpApplication->shouldReceive('isApsg')
            ->withNoArgs()
            ->andReturn($isApsg);
        $irhpApplication->shouldReceive('isUnderConsideration')
            ->withNoArgs()
            ->andReturn($isUnderConsideration);
        $irhpApplication->shouldReceive('isOngoing')
            ->withNoArgs()
            ->andReturn($isOngoing);

        $applicationFeeType = m::mock(FeeTypeEntity::class);
        $applicationFeeType->shouldReceive('getFixedValue')
            ->andReturn($applicationFeePerPermit);

        $issueFeeType = m::mock(FeeTypeEntity::class);
        $issueFeeType->shouldReceive('getFixedValue')
            ->andReturn(self::ISSUE_FEE_PER_PERMIT);

        $feeTypeRepo = m::mock(FeeTypeRepository::class);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with(FeeTypeEntity::FEE_TYPE_ECMT_APP_PRODUCT_REF)
            ->andReturn($applicationFeeType);
        $feeTypeRepo->shouldReceive('getLatestByProductReference')
            ->with(FeeTypeEntity::FEE_TYPE_ECMT_SHORT_TERM_ISSUE_PRODUCT_REF)
            ->andReturn($issueFeeType);

        $noOfPermits = m::mock(NoOfPermits::class);

        $noOfPermitsFactory = m::mock(NoOfPermitsFactory::class);
        $noOfPermitsFactory->shouldReceive('create')
            ->with(
                $expectedMaxCanApplyFor,
                $maxPermitted,
                $applicationFeePerPermit,
                $expectedIssueFeePerPermit,
                $expectedSkipAvailabilityValidation
            )
            ->once()
            ->andReturn($noOfPermits);

        $emissionsCategoryConditionalAdder = m::mock(EmissionsCategoryConditionalAdder::class);
        $emissionsCategoryConditionalAdder->shouldReceive('addIfRequired')
            ->with(
                $noOfPermits,
                'euro5',
                $requiredEuro5,
                RefData::EMISSIONS_CATEGORY_EURO5_REF,
                $stockId
            )
            ->once()
            ->ordered();
        $emissionsCategoryConditionalAdder->shouldReceive('addIfRequired')
            ->with(
                $noOfPermits,
                'euro6',
                $requiredEuro6,
                RefData::EMISSIONS_CATEGORY_EURO6_REF,
                $stockId
            )
            ->once()
            ->ordered();

        $stockAvailabilityCounter = m::mock(StockAvailabilityCounter::class);
        $stockAvailabilityCounter->shouldReceive('getCount')
            ->with($stockId)
            ->andReturn($permitsRemaining);

        $stockLicenceMaxPermittedCounter = m::mock(StockLicenceMaxPermittedCounter::class);
        $stockLicenceMaxPermittedCounter->shouldReceive('getCount')
            ->with($irhpPermitStock, $licence)
            ->andReturn($maxPermitted);

        $noOfPermitsGenerator = new NoOfPermitsGenerator(
            $feeTypeRepo,
            $noOfPermitsFactory,
            $emissionsCategoryConditionalAdder,
            $stockAvailabilityCounter,
            $stockLicenceMaxPermittedCounter
        );

        $this->assertSame(
            $noOfPermits,
            $noOfPermitsGenerator->generate($elementGeneratorContext)
        );
    }

    public static function dpGenerate(): array
    {
        return [
            [43, 19, 19, true, true, true, false, 'N/A'],
            [17, 40, 17, true, true, true, false, 'N/A'],
            [17, 40, 17, false, true, false, false, 'N/A'],
            [17, 40, 17, true, false, false, false, 'N/A'],
            [17, 40, 17, false, false, false, false, 'N/A'],
            [43, 19, 19, true, true, true, true, self::ISSUE_FEE_PER_PERMIT],
            [17, 40, 17, true, true, true, true, self::ISSUE_FEE_PER_PERMIT],
            [17, 40, 17, false, true, false, true, self::ISSUE_FEE_PER_PERMIT],
            [17, 40, 17, true, false, false, true, self::ISSUE_FEE_PER_PERMIT],
            [17, 40, 17, false, false, false, true, self::ISSUE_FEE_PER_PERMIT],
        ];
    }
}
