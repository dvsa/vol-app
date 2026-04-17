<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\LicCorrespondenceAddress;

/**
 * Licence holder correspondence address bookmark test
 */
class LicCorrespondenceAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new LicCorrespondenceAddress();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new LicCorrespondenceAddress();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            [
                [
                    'correspondenceCd' => [
                        'address' => [
                            'addressLine1' => 'Line 1',
                            'addressLine2' => 'Line 2',
                            'addressLine3' => 'Line 3',
                            'addressLine4' => 'Line 4',
                            'town' => 'Leeds',
                            'postcode' => 'LS9 6NF',
                        ]
                    ]
                ],
                'Line 1, Line 2, Line 3, Line 4, Leeds, LS9 6NF'
            ],
            [
                [],
                ''
            ],
        ];
    }
}
