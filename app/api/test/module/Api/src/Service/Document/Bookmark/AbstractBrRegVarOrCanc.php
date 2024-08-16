<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Bus\BusReg;
use Dvsa\Olcs\Api\Service\Document\Bookmark\Base\DynamicBookmark;

/**
 * AbstractBrRegVarOrCanc test
 */
class AbstractBrRegVarOrCanc extends \PHPUnit\Framework\TestCase
{
    protected $new;
    protected $vary;
    protected $cancel;
    protected $bookmarkClass;

    /**
     * test getQuery
     */
    public function testGetQuery()
    {
        $bookmark = $this->getBookmark();

        $this->assertInstanceOf($this->bookmarkClass, $bookmark);

        $this->assertInstanceOf(
            \Dvsa\Olcs\Transfer\Query\QueryInterface::class,
            $bookmark->getQuery(['busRegId' => 123])
        );
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender($data, $expected)
    {
        $bookmark = $this->getBookmark();
        $bookmark->setData($data);
        $this->assertInstanceOf($this->bookmarkClass, $bookmark);

        if ($expected === false) {
            $this->expectException(
                \Exception::class
            );
        }
        $this->assertEquals($expected, $bookmark->render());
    }

    /**
     * @return array
     */
    public function renderDataProvider()
    {
        return [
            [
                [],
                false
            ],
            [
                [
                    'status' => ''
                ],
                false
            ],
            [
                [
                    'status' => ['id' => 'foo'],
                ],
                false
            ],
            [
                [
                    'status' => ['id' => BusReg::STATUS_NEW],
                ],
                $this->new
            ],
            [
                [
                    'status' => ['id' => BusReg::STATUS_CANCEL],
                ],
                $this->cancel
            ],
            [
                [
                    'status' => ['id' => BusReg::STATUS_VAR],
                ],
                $this->vary
            ],
            [
                [
                    'status' => ['id' => BusReg::STATUS_EXPIRED],
                ],
                false
            ],
        ];
    }

    /**
     * @return DynamicBookmark
     */
    public function getBookmark()
    {
        return new $this->bookmarkClass();
    }
}
