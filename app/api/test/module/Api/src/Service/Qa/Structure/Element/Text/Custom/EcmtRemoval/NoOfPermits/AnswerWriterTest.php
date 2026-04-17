<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits;

use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitApplication as IrhpPermitApplicationRepository;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Service\Qa\Structure\Element\Text\Custom\EcmtRemoval\NoOfPermits\AnswerWriter;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * AnswerWriterTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AnswerWriterTest extends MockeryTestCase
{
    public function testWrite(): void
    {
        $permitsRequired = 345;

        $irhpPermitApplicationEntity = m::mock(IrhpPermitApplicationEntity::class);
        $irhpPermitApplicationEntity->shouldReceive('updatePermitsRequired')
            ->with($permitsRequired)
            ->once()
            ->globally()
            ->ordered();

        $irhpApplicationEntity = m::mock(IrhpApplicationEntity::class);
        $irhpApplicationEntity->shouldReceive('getFirstIrhpPermitApplication')
            ->andReturn($irhpPermitApplicationEntity);

        $irhpPermitApplicationRepo = m::mock(IrhpPermitApplicationRepository::class);
        $irhpPermitApplicationRepo->shouldReceive('save')
            ->with($irhpPermitApplicationEntity)
            ->once()
            ->globally()
            ->ordered();

        $answerWriter = new AnswerWriter($irhpPermitApplicationRepo);
        $answerWriter->write($irhpApplicationEntity, $permitsRequired);
    }
}
