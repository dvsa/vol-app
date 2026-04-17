<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegStatus;

/**
 * BrRegStatus test
 */
class BrRegStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new BrRegStatus();
        $this->assertInstanceOf(Qry::class, $bookmark->getQuery(['busRegId' => 123]));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new BrRegStatus();
        $bookmark->setData($data);
        $this->assertEquals($expected, $bookmark->render());
    }

    /**
     * @return array
     */
    public static function renderDataProvider(): array
    {
        return [
            [
                [],
                null
            ],
            [
                [
                    'status' => [
                        'description' => 'bus status'
                    ]
                ],
                'bus status'
            ]
        ];
    }
}
