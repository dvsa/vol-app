<?php

namespace CommonTest\Common\Service\Table\Formatter;

use Common\Service\Table\Formatter\TaskAllocationCriteria;

/**
 * Criteria test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaskAllocationCriteriaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @dataProvider provider
     */
    public function testFormat($expected, $data): void
    {
        $sut = new TaskAllocationCriteria();

        $this->assertSame($expected, $sut->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            // expected, data
            ['Goods, MLH', ['goodsOrPsv' => ['id' => 'lcat_gv'], 'isMlh' => true]],
            ['Goods, Non-MLH', ['goodsOrPsv' => ['id' => 'lcat_gv'], 'isMlh' => false]],
            ['PSV', ['goodsOrPsv' => ['id' => 'lcat_psv']]],
            ['N/A', ['goodsOrPsv' => ['id' => 'XXXX'], 'isMlh' => true]],
            ['N/A', ['goodsOrPsv' => null]],
        ];
    }
}
