<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\OpDetails;

/**
 * OpDetails test
 *
 * @author Josh Curtis <josh.curtis@valtech.co.uk>
 */
class OpDetailsTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new OpDetails();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
        $this->assertSame(
            [
                'bundle' => [
                    'correspondenceCd' => [
                        'address'
                    ],
                    'organisation',
                    'tradingNames',
                ],
                'id' => 123
            ],
            $query->getArrayCopy()
        );
    }

    public static function dpRenderValidDataProvider(): array
    {
        return [
            [
                "Mr Testy Test\nTesting Test Limited\nT/A: Trading Test Limited \n" .
                "Test\nTest Place\nTest\nTesting\ntest",
                [
                    'organisation' => [
                        'name' => 'Testing Test Limited',
                    ],
                    'tradingNames' => [
                        [
                            'name' => 'Trading Test Limited'
                        ]
                    ],
                    'correspondenceCd' => [
                        'fao' => 'Mr Testy Test',
                        'address' => [
                            'addressLine1' => 'Test',
                            'addressLine2' => 'Test Place',
                            'addressLine3' => '',
                            'addressLine4' => 'Test',
                            'town' => 'Testing',
                            'postcode' => 'test'
                        ]
                    ]
                ]
            ],
            [
                "Testing Test Limited\n" .
                "Test\nTest Place\nTest\nTesting\ntest",
                [
                    'organisation' => [
                        'name' => 'Testing Test Limited',
                    ],
                    'tradingNames' => [],
                    'correspondenceCd' => [
                        'fao' => '',
                        'address' => [
                            'addressLine1' => 'Test',
                            'addressLine2' => 'Test Place',
                            'addressLine3' => '',
                            'addressLine4' => 'Test',
                            'town' => 'Testing',
                            'postcode' => 'test'
                        ]
                    ]
                ]
            ]
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderValidDataProvider')]
    public function testRender(mixed $expected, mixed $results): void
    {
        $bookmark = new OpDetails();
        $bookmark->setData($results);

        $this->assertEquals($expected, $bookmark->render());
    }
}
