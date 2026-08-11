<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegStatus;

/**
 * BrRegStatus test
 */
final class BrRegStatusTest extends \PHPUnit\Framework\TestCase
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
     * @return \Iterator<(int | string), mixed>
     */
    public static function renderDataProvider(): \Iterator
    {
        yield [
            [],
            null
        ];
        yield [
            [
                'status' => [
                    'description' => 'bus status'
                ]
            ],
            'bus status'
        ];
    }
}
