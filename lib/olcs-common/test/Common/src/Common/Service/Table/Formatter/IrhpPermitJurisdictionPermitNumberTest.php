<?php

/**
 * Irhp Permit Jurisdiction Permit Number Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitJurisdictionPermitNumber;
use Mockery\Adapter\Phpunit\MockeryTestCase;

final class IrhpPermitJurisdictionPermitNumberTest extends MockeryTestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('IrhpPermitJurisdictionFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('formatProvider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new IrhpPermitJurisdictionPermitNumber()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function formatProvider(): \Iterator
    {
        yield 'Empty Quota Number' => [
            [
                'quotaNumber' => '',
                'id' => 111,
            ],
            "<input type='number' value='0' name='trafficAreas[111]' />"
        ];
        yield 'Non-Empty Quota Number' => [
            [
                'quotaNumber' => '100',
                'id' => 222,
            ],
            "<input type='number' value='100' name='trafficAreas[222]' />"
        ];
    }
}
