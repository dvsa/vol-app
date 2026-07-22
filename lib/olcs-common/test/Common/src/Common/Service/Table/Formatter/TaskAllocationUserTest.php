<?php

declare(strict_types=1);

namespace CommonTest\Common\Service\Table\Formatter;

use Common\Service\Helper\DataHelperService;
use Common\Service\Table\Formatter\TaskAllocationUser;

/**
 * User test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
final class TaskAllocationUserTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($expected, $data): void
    {
        $sut = new TaskAllocationUser(new DataHelperService());

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
        yield ['Mary Jones', ['forename' => 'Mary', 'familyName' => 'Jones']];
        yield ['Unassigned', ['forename' => '', 'familyName' => '', 'taskAlphaSplits' => null]];
        yield ['Unassigned', ['forename' => '', 'familyName' => '', 'taskAlphaSplits' => []]];
        yield ['[Alpha split]', ['forename' => '', 'familyName' => '', 'taskAlphaSplits' => [1, 2]]];
    }
}
