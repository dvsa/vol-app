<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * AbstractBrRegOrVary test
 */
class AbstractBrRegOrVary extends \PHPUnit\Framework\TestCase
{
    protected const RENDER_REG = '';
    protected const RENDER_VARY = '';
    protected $bookmarkClass;

    public function testGetQuery(): void
    {
        $bookmark = $this->getBookmark([]);
        $this->assertInstanceOf(Qry::class, $bookmark->getQuery(['busRegId' => 123]));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('renderDataProvider')]
    public function testRender(mixed $data, mixed $expected): void
    {
        $bookmark = $this->getBookmark($data);
        $this->assertEquals($expected, $bookmark->render());
    }

    /**
     * test exception thrown when data is empty
     */
    public function testRenderWithEmptyData(): void
    {
        $this->expectException(
            \Exception::class
        );

        $bookmark = $this->getBookmark([]);
        $bookmark->render();
    }

    /**
     * @return array
     */
    public static function renderDataProvider(): array
    {
        return [
            [
                [
                    'variationNo' => 0,
                ],
                static::RENDER_REG
            ],
            [
                [
                    'variationNo' => 1,
                ],
                static::RENDER_VARY
            ],
            [
                [
                    'variationNo' => 222,
                ],
                static::RENDER_VARY
            ],
        ];
    }

    /**
     * Returns a bookmark populated with data
     *
     * @param array $data data
     *
     * @return DynamicBookmark
     */
    public function getBookmark(mixed $data): mixed
    {
        /** @var DynamicBookmark $bookmark */
        $bookmark = new $this->bookmarkClass();
        $bookmark->setData($data);

        return $bookmark;
    }
}
