<?php

/**
 * Irhp Permit Sector Quota Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitSectorQuota;

class IrhpPermitSectorQuotaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group IrhpPermitSectorFormatter
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new IrhpPermitSectorQuota())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'Empty Quota Number' => [
                [
                    'quotaNumber' => '',
                    'id' => 111,
                ],
                "<input type='number' value='0' name='sectors[111]' />"
            ],
            'Non-Empty Quota Number' => [
                [
                    'quotaNumber' => '100',
                    'id' => 222,
                ],
                "<input type='number' value='100' name='sectors[222]' />"
            ],
        ];
    }
}
