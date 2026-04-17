<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Custom\Ecmt;

use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\QaContext;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Custom\Ecmt\SectorsAnswerClearer;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * SectorsAnswerClearerTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class SectorsAnswerClearerTest extends MockeryTestCase
{
    public function testSave(): void
    {
        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('clearSectors')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $irhpApplicationRepo = m::mock(IrhpApplicationRepository::class);
        $irhpApplicationRepo->shouldReceive('save')
            ->with($irhpApplication)
            ->once()
            ->globally()
            ->ordered();

        $qaContext = m::mock(QaContext::class);
        $qaContext->shouldReceive('getQaEntity')
            ->withNoArgs()
            ->andReturn($irhpApplication);

        $sectorsAnswerClearer = new SectorsAnswerClearer($irhpApplicationRepo);
        $sectorsAnswerClearer->clear($qaContext);
    }
}
