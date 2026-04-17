<?php

declare(strict_types=1);

/**
 * BkmOperatorAddress1 Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BkmOperatorAddress1 as Sut;

/**
 * BkmOperatorAddress1 Test
 */
class BkmOperatorAddress1Test extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['irfoPsvAuth' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new Sut();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            [
                [
                    'organisation' => [
                        'irfoContactDetails' => [
                            'address' => [
                                'addressLine1' => 'Line 1',
                                'addressLine2' => 'Line 2',
                                'addressLine3' => 'Line 3',
                                'addressLine4' => 'Line 4',
                                'town' => 'Leeds',
                                'postcode' => 'LS9 6NF',
                                'countryCode' => [
                                    'countryDesc' => 'United Kingdom'
                                ]
                            ]
                        ]
                    ]
                ],
                "Line 1\nLine 2\nLine 3\nLine 4\nLeeds\nLS9 6NF\nUnited Kingdom"
            ],
            [
                [],
                ''
            ],
        ];
    }
}
