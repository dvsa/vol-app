<?php

/**
 * Irhp Permit Jurisdiction Permit Number Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitJurisdictionPermitNumber;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class IrhpPermitJurisdictionPermitNumberTest extends MockeryTestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     * @group IrhpPermitJurisdictionFormatter
     *
     * @dataProvider formatProvider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new IrhpPermitJurisdictionPermitNumber())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function formatProvider()
    {
        return [
            'Empty Quota Number' => [
                [
                    'quotaNumber' => '',
                    'id' => 111,
                ],
                "<input type='number' value='0' name='trafficAreas[111]' />"
            ],
            'Non-Empty Quota Number' => [
                [
                    'quotaNumber' => '100',
                    'id' => 222,
                ],
                "<input type='number' value='100' name='trafficAreas[222]' />"
            ],
        ];
    }
}
