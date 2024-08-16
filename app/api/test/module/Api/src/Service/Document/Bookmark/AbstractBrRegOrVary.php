<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * AbstractBrRegOrVary test
 */
class AbstractBrRegOrVary extends \PHPUnit\Framework\TestCase
{
    protected $renderReg;
    protected $renderVary;
    protected $bookmarkClass;

    public function testGetQuery()
    {
        $bookmark = $this->getBookmark([]);
        $this->assertInstanceOf(Qry::class, $bookmark->getQuery(['busRegId' => 123]));
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender($data, $expected)
    {
        $bookmark = $this->getBookmark($data);
        $this->assertEquals($expected, $bookmark->render());
    }

    /**
     * test exception thrown when data is empty
     */
    public function testRenderWithEmptyData()
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
    public function renderDataProvider()
    {
        return [
            [
                [
                    'variationNo' => 0,
                ],
                $this->renderReg
            ],
            [
                [
                    'variationNo' => 1,
                ],
                $this->renderVary
            ],
            [
                [
                    'variationNo' => 222,
                ],
                $this->renderVary
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
    public function getBookmark($data)
    {
        /** @var DynamicBookmark $bookmark */
        $bookmark = new $this->bookmarkClass();
        $bookmark->setData($data);

        return $bookmark;
    }
}
