<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BrNPNo;

/**
 * Br N P No test
 */
class BrNPNoTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new BrNPNo();

        $this->assertInstanceOf(
            \Dvsa\Olcs\Transfer\Query\QueryInterface::class,
            $bookmark->getQuery(['busRegId' => 123])
        );
        $this->assertTrue(is_null($bookmark->getQuery([])));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = new BrNPNo();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function renderDataProvider(): array
    {
        return [
            // no results
            [
                [],
                ''
            ],
            // results without publication
            [
                [
                    'Results' => [
                        ['id' => 1]
                    ]
                ],
                ''
            ],
            // results with publication
            [
                [
                    'Results' => [
                        ['id' => 1, 'publication' => ['publicationNo' => 10]],
                        ['id' => 1, 'publication' => ['publicationNo' => 11]],
                        ['id' => 1, 'publication' => ['publicationNo' => 12]],
                    ]
                ],
                12
            ],
        ];
    }
}
