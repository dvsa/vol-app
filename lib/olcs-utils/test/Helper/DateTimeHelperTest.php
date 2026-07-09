<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Utils\Helper;

use Dvsa\Olcs\Utils\Helper\DateTimeHelper;
use PHPUnit\Framework\TestCase;

final class DateTimeHelperTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestFormat')]
    public function testFormat($date, $format, $expect)
    {
        if ($format !== null) {
            $actual = DateTimeHelper::format($date, $format);
        } else {
            $actual = DateTimeHelper::format($date);
        }

        $this->assertEquals($expect, $actual);
    }

    public static function dpTestFormat(): \Iterator
    {
        $time = strtotime('2017-11-12T13:14:15+0000');
        yield [
            'date' => '2017-11-12T13:14:15+0000',
            'format' =>  'd/m/Y H:i:s',
            'expect' => date('d/m/Y H:i:s', $time),
        ];
        yield [
            'date' => '2017-11-12 13:14:15',
            'format' => 'd/m/Y H:i:s',
            'expect' => date('d/m/Y H:i:s', $time),
        ];
        yield [
            'date' => '2017-11-12 13:14:15',
            'format' => 'g:ia \o\n l jS F Y',
            'expect' => date('g:ia \o\n l jS F Y', $time),
        ];
        yield [
            'date' => '2017-11-12',
            'format' => 'd/m/Y',
            'expect' => date('d/m/Y', $time),
        ];
        yield [
            'date' => 'AAAA-02-12',
            'format' => null,
            'expect' => null,
        ];
    }
}
