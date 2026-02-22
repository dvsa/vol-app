<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\InjectNaptanCodes;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class InjectNaptanCodesTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter
 */
class InjectNaptanCodesTest extends TestCase
{
    public function testFilter(): void
    {
        $sut = new InjectNaptanCodes();

        $input = [
            'stops' => [
                0 => '3200YNA06391',
                1 => '3200YNA00851',
                2 => '60902674',
                3 => '60902812',
                4 => '0500EELYY051'
            ]
        ];

        $expected = [
            320 => '320',
            609 => '609',
            '050' => '050'
        ];

        $return = $sut->filter($input);

        $this->assertEquals($expected, $return['naptan']);
    }
}
