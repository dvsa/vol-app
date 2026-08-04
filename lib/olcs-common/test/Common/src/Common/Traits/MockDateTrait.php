<?php

/**
 * Mock Date Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Traits;

use Mockery as m;

/**
 * Mock Date Trait
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
trait MockDateTrait
{
    /**
     * Helper method
     */
    protected function mockDate($date)
    {

        $mockDateHelper = m::mock(\Common\Service\Helper\DateHelperService::class)->makePartial();

        $mockDateHelper->shouldReceive('getDate')->andReturn($date);

        $dateObj = new \DateTime($date);
        $mockDateHelper->shouldReceive('getDateObject')->withNoArgs()->andReturn($dateObj);

        $this->sm->setService('Helper\Date', $mockDateHelper);
    }
}
