<?php

/**
 * IrhpPermitOrganisationName Test
 */

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitOrganisationName;

class IrhpPermitOrganisationNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     * @group Formatters
     *
     * @dataProvider provider
     */
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, (new IrhpPermitOrganisationName())->format($data));
    }

    /**
     * Data provider
     *
     * @return array
     */
    public function provider()
    {
        return [
            'with value' => [
                [
                    'irhpPermitApplication' => [
                        'relatedApplication' => [
                            'licence' => [
                                'organisation' => [
                                    'name' => 'Organisation name>'
                                ]
                            ]
                        ]
                    ]
                ],
                'Organisation name&gt;',
            ],
            'empty value' => [
                null,
                ''
            ]
        ];
    }
}
