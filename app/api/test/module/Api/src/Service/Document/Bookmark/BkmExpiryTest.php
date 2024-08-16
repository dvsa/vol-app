<?php

/**
 * BkmExpiry Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BkmExpiry as Sut;

/**
 * BkmExpiry Test
 */
class BkmExpiryTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['irfoPsvAuth' => 123]);
        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender($data, $expected)
    {
        $bookmark = new Sut();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public function renderDataProvider()
    {
        return [
            [
                [
                    'expiryDate' => new \DateTime('2015-12-30')
                ],
                '30 December 2015'
            ],
            [
                [
                    'expiryDate' => '2015-12-30'
                ],
                '30 December 2015'
            ],
            [
                [],
                ''
            ],
        ];
    }
}
