<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark\Formatter;

use Dvsa\Olcs\Api\Service\Document\Bookmark\Formatter;

/**
 * Time formatter test
 */
class TimeTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @dataProvider nameProvider
     */
    public function testFormat($input, $expected)
    {
        $this->assertEquals(
            $expected,
            Formatter\Time::format((array)$input)
        );
    }

    public function nameProvider()
    {
        return [
            ['XX', null],
            ['2017-01-10T12:45:22+00:00', '12:45'],
            ['2017-06-10T12:45:22+00:00', '13:45'],
        ];
    }
}
