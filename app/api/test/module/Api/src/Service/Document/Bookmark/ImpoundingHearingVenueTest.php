<?php

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
    public function testGetQuery()
    {
        $bookmark = new ImpoundingHearingVenue();
        $query = $bookmark->getQuery(['impounding' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
        $this->assertTrue(is_null($bookmark->getQuery([])));
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender($data, $expected)
    {
        $bookmark = new ImpoundingHearingVenue();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public function renderDataProvider()
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
