<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Fees;

use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepository;
use Dvsa\Olcs\Api\Entity\System\SystemParameter;
use Dvsa\Olcs\Api\Service\Permits\Fees\DaysToPayIssueFeeProvider;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * DaysToPayIssueFeeProviderTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class DaysToPayIssueFeeProviderTest extends MockeryTestCase
{
    public function testGetDays(): void
    {
        $daysToPayIssueFee = 10;

        $systemParameterRepo = m::mock(SystemParameterRepository::class);
        $systemParameterRepo->shouldReceive('fetchValue')
            ->with(SystemParameter::PERMITS_DAYS_TO_PAY_ISSUE_FEE)
            ->andReturn($daysToPayIssueFee);

        $daysToPayIssueFeeProvider = new DaysToPayIssueFeeProvider($systemParameterRepo);

        $daysToPayIssueFeeActual = $daysToPayIssueFeeProvider->getDays();
        $this->assertEquals($daysToPayIssueFeeActual, $daysToPayIssueFee);
        $this->assertIsInt($daysToPayIssueFeeActual);
    }
}
