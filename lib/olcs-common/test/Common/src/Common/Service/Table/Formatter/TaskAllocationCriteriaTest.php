<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Formatter;

use Common\Service\Table\Formatter\TaskAllocationCriteria;

/**
 * Criteria test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class TaskAllocationCriteriaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($expected, $data): void
    {
        $sut = new TaskAllocationCriteria();

        $this->assertSame($expected, $sut->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        // expected, data
        yield ['Goods, MLH', ['goodsOrPsv' => ['id' => 'lcat_gv'], 'isMlh' => true]];
        yield ['Goods, Non-MLH', ['goodsOrPsv' => ['id' => 'lcat_gv'], 'isMlh' => false]];
        yield ['PSV', ['goodsOrPsv' => ['id' => 'lcat_psv']]];
        yield ['N/A', ['goodsOrPsv' => ['id' => 'XXXX'], 'isMlh' => true]];
        yield ['N/A', ['goodsOrPsv' => null]];
    }
}
