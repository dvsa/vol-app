<?php

/**
 * BkmOperatorName Test
 */

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\BkmOperatorName as Sut;

/**
 * BkmOperatorName Test
 */
class BkmOperatorNameTest extends \PHPUnit\Framework\TestCase
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
                    'organisation' => [
                        'name' => 'org name'
                    ]
                ],
                'org name'
            ],
            [
                [],
                ''
            ],
        ];
    }
}
