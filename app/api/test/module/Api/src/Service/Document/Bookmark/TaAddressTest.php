<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TaAddress;

/**
 * TA Address test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TaAddressTest extends \PHPUnit\Framework\TestCase
{
    /**
     * Test getQuery
     */
    public function testGetQuery(): void
    {
        $bookmark = new TaAddress();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    /**
     * Test render
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProvider')]
    public function testRender(mixed $query, mixed $expected): void
    {
        $bookmark = new TaAddress();
        $bookmark->setData($query);

        $this->assertEquals(
            $expected,
            $bookmark->render()
        );
    }

    /**
     * Data provider
     *
     * @return array
     */
    public static function dataProvider(): array
    {
        return [
            'licence' => [
                [
                    'trafficArea' => [
                        'name' => 'TA Address 1',
                        'contactDetails' => [
                            'address' => [
                                'addressLine1' => 'Line 1',
                                'addressLine2' => 'Line 2',
                                'addressLine3' => 'Line 3',
                                'addressLine4' => 'Line 4',
                                'postcode' => 'LS2 4DD'
                            ]
                        ]
                    ]
                ],
                "TA Address 1\nLine 1\nLine 2\nLine 3\nLine 4\nLS2 4DD"
            ],
            'empty' => [
                [
                    'trafficArea' => [
                        'name' => 'TA Adress 3'
                    ]
                ],
                "TA Adress 3"
            ]
        ];
    }
}
