<?php

/**
 * No of Permits Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Form\Elements\Custom;

use Common\Form\Elements\Custom\NoOfPermits;
use PHPUnit\Framework\TestCase;

/**
 * No of Permits Test
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
final class NoOfPermitsTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHasNonZeroValue')]
    public function testHasNonZeroValue($value, $expectedResult): void
    {
        $sut = new NoOfPermits();
        $sut->setValue($value);

        $this->assertEquals($expectedResult, $sut->hasNonZeroValue());
    }

    /**
     * @return \Iterator<(int | string), array<(bool | int)>>
     *
     * @psalm-return list{list{0, false}, list{1, true}, list{2, true}}
     */
    public static function dpTestHasNonZeroValue(): \Iterator
    {
        yield [0, false];
        yield [1, true];
        yield [2, true];
    }
}
