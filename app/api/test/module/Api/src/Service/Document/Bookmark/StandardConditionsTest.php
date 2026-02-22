<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Service\Document\Bookmark\StandardConditions;

/**
 * Standard conditions test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class StandardConditionsTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new StandardConditions();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $niFlag, mixed $licenceType, mixed $vehicleType, mixed $path): void
    {
        $bookmark = $this->createPartialMock(StandardConditions::class, ['getSnippet']);

        $bookmark->expects($this->any())
            ->method('getSnippet')
            ->with($path)
            ->willReturn('snippet');

        $bookmark->setData(
            [
                'niFlag' => $niFlag,
                'licenceType' => [
                    'id' => $licenceType
                ],
                'vehicleType' => [
                    'id' => $vehicleType
                ],
            ]
        );

        $this->assertEquals('snippet', $bookmark->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            [
                'N',
                Licence::LICENCE_TYPE_RESTRICTED,
                RefData::APP_VEHICLE_TYPE_HGV,
                'GB_RESTRICTED_LICENCE_CONDITIONS'
            ],
            [
                'N',
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_HGV,
                'GB_STANDARD_LICENCE_CONDITIONS'
            ],
            [
                'N',
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_MIXED,
                'GB_STANDARD_INT_LICENCE_CONDITIONS'
            ],
            [
                'N',
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_LGV,
                'GB_STANDARD_INT_LGV_LICENCE_CONDITIONS'
            ],
            [
                'Y',
                Licence::LICENCE_TYPE_RESTRICTED,
                RefData::APP_VEHICLE_TYPE_HGV,
                'NI_RESTRICTED_LICENCE_CONDITIONS'
            ],
            [
                'Y',
                Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                RefData::APP_VEHICLE_TYPE_HGV,
                'NI_STANDARD_LICENCE_CONDITIONS'
            ],
            [
                'Y',
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_MIXED,
                'NI_STANDARD_INT_LICENCE_CONDITIONS'
            ],
            [
                'Y',
                Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
                RefData::APP_VEHICLE_TYPE_LGV,
                'NI_STANDARD_INT_LGV_LICENCE_CONDITIONS'
            ],
        ];
    }
}
