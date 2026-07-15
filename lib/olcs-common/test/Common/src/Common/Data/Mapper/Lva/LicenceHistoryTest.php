<?php

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\LicenceHistory;

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class LicenceHistoryTest extends \PHPUnit\Framework\TestCase
{
    public function testMapFromResult(): void
    {
        $input = [
            'foo' => 'bar'
        ];

        $output = LicenceHistory::mapFromResult($input);

        $expected = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertSame($expected, $output);
    }
}
