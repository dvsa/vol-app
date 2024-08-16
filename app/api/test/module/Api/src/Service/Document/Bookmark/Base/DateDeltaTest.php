<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base;

use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DateDelta;
use Dvsa\OlcsTest\Api\Service\Document\Bookmark\Base\Stub\DateDeltaStub;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DateDelta
 */
class DateDeltaTest extends MockeryTestCase
{
    public function test()
    {
        $sut = new DateDeltaStub();

        $date = new DateTime();
        $date->sub(new \DateInterval('P' . abs(DateDeltaStub::DELTA) . 'D'));

        static::assertEquals(
            $date->format(DateDelta::FORMAT),
            $sut->render()
        );
    }
}
