<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\CandidatePermits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitStock;
use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\ApggCandidatePermitsCreator;
use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\IrhpCandidatePermitsCreator;
use Dvsa\Olcs\Api\Service\Permits\Scoring\CandidatePermitsCreator as ScoringCandidatePermitsCreator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * IrhpCandidatePermitsCreatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class IrhpCandidatePermitsCreatorTest extends MockeryTestCase
{
    private $irhpApplication;

    private $scoringCandidatePermitsCreator;

    private $apggCandidatePermitsCreator;

    private $irhpCandidatePermitsCreator;

    public function setUp(): void
    {
        $this->irhpApplication = m::mock(IrhpApplication::class);

        $this->scoringCandidatePermitsCreator = m::mock(ScoringCandidatePermitsCreator::class);

        $this->apggCandidatePermitsCreator = m::mock(ApggCandidatePermitsCreator::class);

        $this->irhpCandidatePermitsCreator = new IrhpCandidatePermitsCreator(
            $this->scoringCandidatePermitsCreator,
            $this->apggCandidatePermitsCreator
        );
    }

    public function testCreateIfRequiredApsg(): void
    {
        $requiredEuro5 = 7;
        $requiredEuro6 = 10;

        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('getRequiredEuro5')
            ->withNoArgs()
            ->andReturn($requiredEuro5);
        $irhpPermitApplication->shouldReceive('getRequiredEuro6')
            ->withNoArgs()
            ->andReturn($requiredEuro6);

        $this->irhpApplication->shouldReceive('getCandidatePermitCreationMode')
            ->withNoArgs()
            ->andReturn(IrhpPermitStock::CANDIDATE_MODE_APSG);
        $this->irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $this->scoringCandidatePermitsCreator->shouldReceive('create')
            ->with($irhpPermitApplication, $requiredEuro5, $requiredEuro6)
            ->once();

        $this->irhpCandidatePermitsCreator->createIfRequired($this->irhpApplication);
    }

    public function testCreateIfRequiredApgg(): void
    {
        $this->irhpApplication->shouldReceive('getCandidatePermitCreationMode')
            ->withNoArgs()
            ->andReturn(IrhpPermitStock::CANDIDATE_MODE_APGG);

        $this->apggCandidatePermitsCreator->shouldReceive('create')
            ->with($this->irhpApplication)
            ->once();

        $this->irhpCandidatePermitsCreator->createIfRequired($this->irhpApplication);
    }
}
