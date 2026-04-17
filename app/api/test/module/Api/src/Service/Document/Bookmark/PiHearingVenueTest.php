<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\PiHearingVenue;

/**
 * Pi Hearing Venue test
 */
class PiHearingVenueTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new PiHearingVenue();
        $query = $bookmark->getQuery(['hearing' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
        $this->assertTrue(is_null($bookmark->getQuery([])));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new PiHearingVenue();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            [
                [
                    'venue' => ['name' => 'pi venue'],
                    'venueOther' => 'other venue'
                ],
                'pi venue'
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
