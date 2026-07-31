<?php

/**
 * Irhp Permit Sector Quota Test
 *
 * @author Scott Callaway <scott.callaway@capgemini.com>
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitSectorQuota;

final class IrhpPermitSectorQuotaTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\Group('IrhpPermitSectorFormatter')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new IrhpPermitSectorQuota()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'Empty Quota Number' => [
            [
                'quotaNumber' => '',
                'id' => 111,
            ],
            "<input type='number' value='0' name='sectors[111]' />"
        ];
        yield 'Non-Empty Quota Number' => [
            [
                'quotaNumber' => '100',
                'id' => 222,
            ],
            "<input type='number' value='100' name='sectors[222]' />"
        ];
    }
}
