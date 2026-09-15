<?php

/**
 * IrhpPermitOrganisationName Test
 */

declare(strict_types=1);

namespace CommonTest\Service\Table\Formatter;

use Common\Service\Table\Formatter\IrhpPermitOrganisationName;

final class IrhpPermitOrganisationNameTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test the format method
     *
     *
     */
    #[\PHPUnit\Framework\Attributes\Group('Formatters')]
    #[\PHPUnit\Framework\Attributes\DataProvider('provider')]
    public function testFormat($data, $expected): void
    {
        $this->assertEquals($expected, new IrhpPermitOrganisationName()->format($data));
    }

    /**
     * Data provider
     *
     * @return \Iterator<(int | string), mixed>
     */
    public static function provider(): \Iterator
    {
        yield 'with value' => [
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
        ];
        yield 'empty value' => [
            null,
            ''
        ];
    }
}
