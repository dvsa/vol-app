<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\SubjectOperatingCentreAddress as Sut;

/**
 * SubjectOperatingCentreAddressTest
 */
class SubjectOperatingCentreAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['licence' => 123]);
        $this->assertEquals(
            ['id' => 123, 'bundle' => ['operatingCentres' => ['operatingCentre' => ['address']]]],
            $query->getArrayCopy()
        );
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
        $address1 = ['addressLine1' => 'A1_LINE1', 'postcode' => 'P1 1QQ'];
        $address2 = ['addressLine1' => 'A2_LINE1','addressLine2' => 'A2_LINE2', 'postcode' => 'P2 1QQ'];

        return [
            'No operating centres'  => [null, ''],
            'One operating centre'  => [
                [
                    'operatingCentres' => [['operatingCentre' => ['address' => $address1]]]
                ],
                'A1_LINE1, P1 1QQ'
            ],
            'Two operating centres' => [
                [
                    'operatingCentres' => [
                        ['operatingCentre' => ['address' => $address1]],
                        ['operatingCentre' => ['address' => $address2]],
                    ]
                ],
                "A1_LINE1, P1 1QQ\nA2_LINE1, A2_LINE2, P2 1QQ"
            ]
        ];
    }
}
