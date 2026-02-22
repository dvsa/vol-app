<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\CandidatePermits;

use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\ApggCandidatePermitsCreator;
use Dvsa\Olcs\Api\Service\Permits\CandidatePermits\ApggEmissionsCatCandidatePermitsCreator;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * ApggCandidatePermitsCreatorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class ApggCandidatePermitsCreatorTest extends MockeryTestCase
{
    public function testCreate(): void
    {
        $irhpApplication = m::mock(IrhpApplication::class);

        $apggEmissionsCatCandidatePermitsCreator = m::mock(ApggEmissionsCatCandidatePermitsCreator::class);
        $apggEmissionsCatCandidatePermitsCreator->shouldReceive('createIfRequired')
            ->with($irhpApplication, RefData::EMISSIONS_CATEGORY_EURO5_REF)
            ->once();
        $apggEmissionsCatCandidatePermitsCreator->shouldReceive('createIfRequired')
            ->with($irhpApplication, RefData::EMISSIONS_CATEGORY_EURO6_REF)
            ->once();

        $apggCandidatePermitsCreator = new ApggCandidatePermitsCreator($apggEmissionsCatCandidatePermitsCreator);
        $apggCandidatePermitsCreator->create($irhpApplication);
    }
}
