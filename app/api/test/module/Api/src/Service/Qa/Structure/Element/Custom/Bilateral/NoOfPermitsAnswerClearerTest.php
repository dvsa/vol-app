<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Bilateral;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication;
use Dvsa\Olcs\Api\Service\Permits\Bilateral\ApplicationFeesClearer;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Bilateral\NoOfPermitsAnswerClearer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsAnswerClearerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsAnswerClearerTest extends MockeryTestCase
{
    public function testGetRepresentation(): void
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplication::class);
        $irhpPermitApplication->shouldReceive('clearBilateralRequired')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $applicationFeesClearer = m::mock(ApplicationFeesClearer::class);
        $applicationFeesClearer->shouldReceive('clear')
            ->with($irhpPermitApplication)
            ->once();

        $irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);
        $irhpPermitApplicationRepo->shouldReceive('save')
            ->with($irhpPermitApplication)
            ->once()
            ->globally()
            ->ordered();

        $noOfPermitsAnswerClearer = new NoOfPermitsAnswerClearer($applicationFeesClearer, $irhpPermitApplicationRepo);

        $noOfPermitsAnswerClearer->clear($qaContext);
    }
}
