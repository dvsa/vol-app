<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format;

use Dvsa\Olcs\Api\Service\Ebsr\Filter\Format\Subsidy;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class SubsidyTest
 * @package Dvsa\OlcsTest\Api\Service\Ebsr\Filter\Format
 */
class SubsidyTest extends TestCase
{
    /**
     * @param $expected
     * @param $value
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideFilter')]
    public function testFilter(mixed $expected, mixed $value): void
    {
        $sut = new Subsidy();

        $result = $sut->filter(['subsidised' => $value]);
        $this->assertEquals($expected, $result['subsidised']);
    }

    public static function provideFilter(): array
    {
        return [
            ['bs_no', 'none'],
            ['bs_yes', 'full'],
            ['bs_in_part', 'partial'],
            ['bs_no', null]
        ];
    }
}
