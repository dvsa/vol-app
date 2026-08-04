<?php

/**
 * Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

declare(strict_types=1);

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\CompanySubsidiary;

/**
 * Company Subsidiary
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
final class CompanySubsidiaryTest extends \PHPUnit\Framework\TestCase
{
    public function testMapFromResult(): void
    {
        $input = [
            'foo' => 'bar'
        ];

        $output = CompanySubsidiary::mapFromResult($input);

        $expected = [
            'data' => [
                'foo' => 'bar'
            ]
        ];

        $this->assertEquals($expected, $output);
    }
}
