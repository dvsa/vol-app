<?php

/**
 * Irhp Permit Jurisdiction Traffic Area Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitJurisdictionTrafficArea;

final class IrhpPermitJurisdictionTrafficAreaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('IrhpPermitJurisdictionFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new IrhpPermitJurisdictionTrafficArea()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'basic traffic area' => [
            [
                'trafficArea' => [
                    'name' => 'North East of England'
                ],
            ],
            'North East of England',
        ];
    }
}
