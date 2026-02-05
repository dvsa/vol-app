<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Util\DateTime;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\Util\DateTime\AddMonthsRoundingDown;

/**
 * Class DateTimeFrontEndTest
 *
 * @package Dvsa\OlcsTest\Api\Domain\Util\DateTime
 */
class DateTimeFrontEndTest extends MockeryTestCase
{
    public function testConstructorGmt(): void
    {
        $date = new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTimeFrontEnd('2017-03-20 09:15:00');
        $this->assertSame('Europe/London', $date->getTimezone()->getName());
        $this->assertSame('09:15', $date->format('H:i'));
    }

    public function testConstructorBst(): void
    {
        $date = new \Dvsa\Olcs\Api\Domain\Util\DateTime\DateTimeFrontEnd('2017-04-20 09:15:00');
        $this->assertSame('Europe/London', $date->getTimezone()->getName());
        $this->assertSame('10:15', $date->format('H:i'));
    }
}
