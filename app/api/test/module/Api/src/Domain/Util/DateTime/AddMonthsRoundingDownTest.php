<?php

/**
 * Add Months Rounding Down Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Util\DateTime;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddMonthsRoundingDown;

/**
 * Add Months Rounding Down Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class AddMonthsRoundingDownTest extends MockeryTestCase
{
    /**
     * @dataProvider dateProvider
     */
    public function testCalculateDate($date, $months, $expected)
    {
        $addMonths = new AddMonthsRoundingDown();
        $this->assertEquals($addMonths->calculateDate($date, $months), $expected);
    }

    public function dateProvider()
    {
        return [
            [new \DateTime('2015-12-31'), 2, new \DateTime('2016-02-29')],
            [new \DateTime('2010-01-12'), 2, new \DateTime('2010-03-12')],
            [new \DateTime('2016-02-29'), -2, new \DateTime('2015-12-29')],
            [new \DateTime('2010-01-12'), -2, new \DateTime('2009-11-12')],
        ];
    }
}
