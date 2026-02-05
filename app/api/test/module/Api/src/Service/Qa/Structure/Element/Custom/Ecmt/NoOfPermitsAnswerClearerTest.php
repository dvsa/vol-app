<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\NoOfPermitsAnswerClearer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * NoOfPermitsAnswerClearerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class NoOfPermitsAnswerClearerTest extends MockeryTestCase
{
    public function testClear(): void
    {
        $irhpPermitApplication = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplication->shouldReceive('clearEmissionsCategoryPermitsRequired')
            ->once()
            ->withNoArgs()
            ->globally()
            ->ordered();

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getFirstIrhpPermitApplication')
            ->withNoArgs()
            ->andReturn($irhpPermitApplication);

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplication);

        $irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);
        $irhpPermitApplicationRepo->shouldReceive('save')
            ->with($irhpPermitApplication)
            ->once()
            ->globally()
            ->ordered();

        $noOfPermitsAnswerClearer = new NoOfPermitsAnswerClearer($irhpPermitApplicationRepo);

        $noOfPermitsAnswerClearer->clear($qaContext);
    }
}
