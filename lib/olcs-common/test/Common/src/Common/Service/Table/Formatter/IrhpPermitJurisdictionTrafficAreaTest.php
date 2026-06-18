<?php

/**
 * Irhp Permit Jurisdiction Traffic Area Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitJurisdictionTrafficArea;

class IrhpPermitJurisdictionTrafficAreaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group IrhpPermitJurisdictionFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new IrhpPermitJurisdictionTrafficArea())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'basic traffic area' => [
                [
                    'trafficArea' => [
                        'name' => 'North East of England'
                    ],
                ],
                'North East of England',
            ]
        ];
    }
}
