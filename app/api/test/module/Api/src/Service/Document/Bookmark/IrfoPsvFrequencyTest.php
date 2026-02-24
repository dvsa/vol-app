<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\IrfoPsvFrequency as Sut;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\IrfoPsvFrequency
 */
class IrfoPsvFrequencyTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['irfoPsvAuth' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpRenderValidDataProvider')]
    public function testRender(mixed $results, mixed $expected): void
    {
        $bookmark = new Sut();
        $bookmark->setData($results);

        $this->assertEquals($expected, $bookmark->render());
    }

    public static function dpRenderValidDataProvider(): array
    {
        return [
            [
                [
                    'journeyFrequency' => [
                        'description' => 'daily'
                    ]
                ],
                'daily',
            ],
            [
                [],
                '',
            ]
        ];
    }
}
