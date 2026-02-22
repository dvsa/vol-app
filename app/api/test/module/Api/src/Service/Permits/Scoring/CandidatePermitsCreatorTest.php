<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Scoring;

use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepository;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Service\Permits\Scoring\CandidatePermitsCreator;
use Dvsa\Olcs\Api\Service\Permits\Scoring\IrhpCandidatePermitFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CandidatePermitsCreatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CandidatePermitsCreatorTest extends MockeryTestCase
{
    private $requiredEuro5 = 2;
    private $requiredEuro6 = 3;

    private $irhpPermitApplication;

    private $euro5EmissionsCategoryRefData;
    private $euro6EmissionsCategoryRefData;

    private $euro5CandidatePermit1;
    private $euro5CandidatePermit2;

    private $euro6CandidatePermit1;
    private $euro6CandidatePermit2;
    private $euro6CandidatePermit3;

    private $irhpCandidatePermitRepo;

    private $systemParameterRepo;

    private $irhpCandidatePermitFactory;

    public function setUp(): void
    {
        $this->irhpPermitApplication = m::mock(IrhpPermitApplication::class);

        $this->euro5EmissionsCategoryRefData = m::mock(RefData::class);
        $this->euro6EmissionsCategoryRefData = m::mock(RefData::class);

        $this->euro5CandidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $this->euro5CandidatePermit2 = m::mock(IrhpCandidatePermit::class);

        $this->euro6CandidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $this->euro6CandidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $this->euro6CandidatePermit3 = m::mock(IrhpCandidatePermit::class);

        $this->irhpCandidatePermitRepo = m::mock(IrhpCandidatePermitRepository::class);
        $this->irhpCandidatePermitRepo->shouldReceive('getRefdataReference')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($this->euro5EmissionsCategoryRefData);
        $this->irhpCandidatePermitRepo->shouldReceive('getRefdataReference')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($this->euro6EmissionsCategoryRefData);
        $this->irhpCandidatePermitRepo->shouldReceive('save')
            ->with($this->euro5CandidatePermit1)
            ->once();
        $this->irhpCandidatePermitRepo->shouldReceive('save')
            ->with($this->euro5CandidatePermit2)
            ->once();
        $this->irhpCandidatePermitRepo->shouldReceive('save')
            ->with($this->euro6CandidatePermit1)
            ->once();
        $this->irhpCandidatePermitRepo->shouldReceive('save')
            ->with($this->euro6CandidatePermit2)
            ->once();
        $this->irhpCandidatePermitRepo->shouldReceive('save')
            ->with($this->euro6CandidatePermit3)
            ->once();

        $this->systemParameterRepo = m::mock(SystemParameterRepository::class);

        $this->irhpCandidatePermitFactory = m::mock(IrhpCandidatePermitFactory::class);
    }

    public function testCreate(): void
    {
        $intensityOfUse = 30;
        $applicationScore = 20;

        $this->irhpPermitApplication->shouldReceive('getPermitIntensityOfUse')
            ->with(null)
            ->andReturn($intensityOfUse);
        $this->irhpPermitApplication->shouldReceive('getPermitApplicationScore')
            ->with(null)
            ->andReturn($applicationScore);

        $this->systemParameterRepo->shouldReceive('fetchValue')
            ->with(SystemParameter::USE_ALT_ECMT_IOU_ALGORITHM)
            ->andReturn(0);

        $this->irhpCandidatePermitFactory->shouldReceive('create')
            ->times($this->requiredEuro5)
            ->with(
                $this->irhpPermitApplication,
                $this->euro5EmissionsCategoryRefData,
                $intensityOfUse,
                $applicationScore
            )
            ->andReturn($this->euro5CandidatePermit1, $this->euro5CandidatePermit2);
        $this->irhpCandidatePermitFactory->shouldReceive('create')
            ->times($this->requiredEuro6)
            ->with(
                $this->irhpPermitApplication,
                $this->euro6EmissionsCategoryRefData,
                $intensityOfUse,
                $applicationScore
            )
            ->andReturn($this->euro6CandidatePermit1, $this->euro6CandidatePermit2, $this->euro6CandidatePermit3);

        $candidatePermitsCreator = new CandidatePermitsCreator(
            $this->irhpCandidatePermitRepo,
            $this->systemParameterRepo,
            $this->irhpCandidatePermitFactory
        );

        $candidatePermitsCreator->create($this->irhpPermitApplication, $this->requiredEuro5, $this->requiredEuro6);
    }

    public function testCreateWithAltIouAlgorithm(): void
    {
        $euro5IntensityOfUse = 30;
        $euro5ApplicationScore = 20;

        $euro6IntensityOfUse = 50;
        $euro6ApplicationScore = 40;

        $this->irhpPermitApplication->shouldReceive('getPermitIntensityOfUse')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5IntensityOfUse);
        $this->irhpPermitApplication->shouldReceive('getPermitApplicationScore')
            ->with(RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->andReturn($euro5ApplicationScore);
        $this->irhpPermitApplication->shouldReceive('getPermitIntensityOfUse')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6IntensityOfUse);
        $this->irhpPermitApplication->shouldReceive('getPermitApplicationScore')
            ->with(RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->andReturn($euro6ApplicationScore);

        $this->systemParameterRepo->shouldReceive('fetchValue')
            ->with(SystemParameter::USE_ALT_ECMT_IOU_ALGORITHM)
            ->andReturn(1);

        $this->irhpCandidatePermitFactory->shouldReceive('create')
            ->times($this->requiredEuro5)
            ->with(
                $this->irhpPermitApplication,
                $this->euro5EmissionsCategoryRefData,
                $euro5IntensityOfUse,
                $euro5ApplicationScore
            )
            ->andReturn($this->euro5CandidatePermit1, $this->euro5CandidatePermit2);
        $this->irhpCandidatePermitFactory->shouldReceive('create')
            ->times($this->requiredEuro6)
            ->with(
                $this->irhpPermitApplication,
                $this->euro6EmissionsCategoryRefData,
                $euro6IntensityOfUse,
                $euro6ApplicationScore
            )
            ->andReturn($this->euro6CandidatePermit1, $this->euro6CandidatePermit2, $this->euro6CandidatePermit3);

        $candidatePermitsCreator = new CandidatePermitsCreator(
            $this->irhpCandidatePermitRepo,
            $this->systemParameterRepo,
            $this->irhpCandidatePermitFactory
        );

        $candidatePermitsCreator->create($this->irhpPermitApplication, $this->requiredEuro5, $this->requiredEuro6);
    }
}
