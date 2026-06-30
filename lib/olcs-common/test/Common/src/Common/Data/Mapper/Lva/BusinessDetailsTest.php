<?php

/**
 * Business Details
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace CommonTest\Data\Mapper\Lva;

use Common\Data\Mapper\Lva\BusinessDetails;

/**
 * Business Details
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsTest extends \PHPUnit\Framework\TestCase
{
    public function testMapFromResult(): void
    {
        $input = [
            'name' => 'Foo ltd',
            'type' => [
                'id' => 'TYPE'
            ],
            'companyOrLlpNo' => '12345678',
            'version' => 11,
            'tradingNames' => [
                ['name' => 'Foo', 'id' => 1],
                ['name' => 'Bar', 'foo' => 'bar']
            ],
            'natureOfBusiness' => 'SIC Code 1',
            'contactDetails' => [
                'address' => [
                    'foo' => 'bar'
                ]
            ],
            'allowEmail' => 'Y'
        ];

        $output = BusinessDetails::mapFromResult($input);

        $expected = [
            'version' => 11,
            'data' => [
                'name' => 'Foo ltd',
                'type' => 'TYPE',
                'companyNumber' => [
                    'company_number' => '12345678'
                ],
                'tradingNames' => [
                    ['name' => 'Foo'],
                    ['name' => 'Bar']
                ],
                'natureOfBusiness' => 'SIC Code 1',
            ],
            'registeredAddress' => [
                'foo' => 'bar'
            ],
            'allow-email' => [
                'allowEmail' => 'Y'
            ]
        ];

        $this->assertEquals($expected, $output);
    }
}
