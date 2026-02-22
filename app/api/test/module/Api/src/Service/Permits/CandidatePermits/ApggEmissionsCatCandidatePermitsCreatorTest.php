<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\CandidatePermits;

use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitRange;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\Allocate\EmissionsStandardCriteria;
use Dvsa\Olcs\Api\Service\Permits\Allocate\EmissionsStandardCriteriaFactory;
use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\ApggCandidatePermitFactory;
use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\ApggEmissionsCatCandidatePermitsCreator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApggEmissionsCatCandidatePermitsCreatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class ApggEmissionsCatCandidatePermitsCreatorTest extends MockeryTestCase
{
    private $irhpPermitApplication;

    private $irhpApplication;

    private $apggCandidatePermitFactory;

    private $irhpCandidatePermitRepo;

    private $emissionsStandardCriteriaFactory;

    private $apggEmissionsCatCandidatePermitsCreator;

    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->irhpApplication = m::mock(IrhpApplication::class);
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($this->irhpPermitApplication);

        $this->apggCandidatePermitFactory = m::mock(ApggCandidatePermitFactory::class);

        $this->irhpCandidatePermitRepo = m::mock(IrhpCandidatePermitRepository::class);

        $this->emissionsStandardCriteriaFactory = m::mock(EmissionsStandardCriteriaFactory::class);

        $this->apggEmissionsCatCandidatePermitsCreator = new ApggEmissionsCatCandidatePermitsCreator(
            $this->apggCandidatePermitFactory,
            $this->irhpCandidatePermitRepo,
            $this->emissionsStandardCriteriaFactory
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpEmissionsCategories')]
    public function testCreateOneOrMoreRequired(mixed $emissionsCategoryId): void
    {
        $permitsRequired = 3;

        $this->irhpPermitApplication->shouldReceive('getRequiredPermitsByEmissionsCategory')
            ->with($emissionsCategoryId)
            ->andReturn($permitsRequired);

        $irhpPermitRange = m::mock(IrhpPermitRange::class);

        $emissionsStandardCriteria = m::mock(EmissionsStandardCriteria::class);

        $this->emissionsStandardCriteriaFactory->shouldReceive('create')
            ->with($emissionsCategoryId)
            ->andReturn($emissionsStandardCriteria);

        $this->irhpApplication->shouldReceive('getAssociatedStock->getFirstAvailableRangePreferWithNoCountries')
            ->with($emissionsStandardCriteria)
            ->andReturn($irhpPermitRange);

        $irhpCandidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit3 = m::mock(IrhpCandidatePermit::class);

        $this->irhpCandidatePermitRepo->shouldReceive('save')
            ->with($irhpCandidatePermit1)
            ->once();
        $this->irhpCandidatePermitRepo->shouldReceive('save')
            ->with($irhpCandidatePermit2)
            ->once();
        $this->irhpCandidatePermitRepo->shouldReceive('save')
            ->with($irhpCandidatePermit3)
            ->once();

        $this->apggCandidatePermitFactory->shouldReceive('create')
            ->with($this->irhpPermitApplication, $irhpPermitRange)
            ->times($permitsRequired)
            ->andReturn($irhpCandidatePermit1, $irhpCandidatePermit2, $irhpCandidatePermit3);

        $this->apggEmissionsCatCandidatePermitsCreator->createIfRequired($this->irhpApplication, $emissionsCategoryId);
    }

    #[\PHPUnit\Framework\Attributes\DoesNotPerformAssertions]
    #[\PHPUnit\Framework\Attributes\DataProvider('dpEmissionsCategories')]
    public function testZeroRequired(mixed $emissionsCategoryId): void
    {
        $permitsRequired = 0;

        $this->irhpPermitApplication->shouldReceive('getRequiredPermitsByEmissionsCategory')
            ->with($emissionsCategoryId)
            ->andReturn($permitsRequired);

        $this->apggEmissionsCatCandidatePermitsCreator->createIfRequired($this->irhpApplication, $emissionsCategoryId);
    }

    public static function dpEmissionsCategories(): array
    {
        return [
            [RefData::EMISSIONS_CATEGORY_EURO5_REF],
            [RefData::EMISSIONS_CATEGORY_EURO6_REF],
        ];
    }
}
