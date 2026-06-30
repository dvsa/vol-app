<?php

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\LicenceHistory;

/**
 * Licence History
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicenceHistoryTest extends \PHPUnit\Framework\TestCase
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

        $this->assertEquals($expected, $output);
    }
}
