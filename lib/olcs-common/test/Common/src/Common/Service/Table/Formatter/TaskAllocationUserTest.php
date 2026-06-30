<?php

namespace CommonTest\Common\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;
use Common\Service\Table\Formatter\TaskAllocationUser;

/**
 * User test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class TaskAllocationUserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @dataProvider provider
     */
    public function testFormat($expected, $data): void
    {
        $sut = new TaskAllocationUser(new DataHelperService());

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
            ['Mary Jones', ['forename' => 'Mary', 'familyName' => 'Jones']],
            ['Unassigned', ['forename' => '', 'familyName' => '', 'taskAlphaSplits' => null]],
            ['Unassigned', ['forename' => '', 'familyName' => '', 'taskAlphaSplits' => []]],
            ['[Alpha split]', ['forename' => '', 'familyName' => '', 'taskAlphaSplits' => [1, 2]]],
        ];
    }
}
