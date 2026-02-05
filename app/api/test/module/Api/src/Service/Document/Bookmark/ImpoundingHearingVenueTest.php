<?php

declare(strict_types=1);

/**
 * Impounding Hearing Venue Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\ImpoundingHearingVenue;

/**
 * Impounding Hearing Venue Test
 */
class ImpoundingHearingVenueTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new ImpoundingHearingVenue();
        $query = $bookmark->getQuery(['impounding' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
        $this->assertTrue(is_null($bookmark->getQuery([])));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new ImpoundingHearingVenue();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            [
                [
                    'venue' => ['name' => 'impounding hearing venue'],
                    'venueOther' => 'other venue'
                ],
                'impounding hearing venue'
            ],
            [
                [
                    'venueOther' => 'other venue'
                ],
                'other venue'
            ],
        ];
    }
}
