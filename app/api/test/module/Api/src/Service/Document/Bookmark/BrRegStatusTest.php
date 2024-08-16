<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\BusRegBundle as Qry;
use Dvsa\Olcs\Api\Service\Document\Bookmark\BrRegStatus;

/**
 * BrRegStatus test
 */
class BrRegStatusTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new BrRegStatus();
        $this->assertInstanceOf(Qry::class, $bookmark->getQuery(['busRegId' => 123]));
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender($data, $expected)
    {
        $bookmark = new BrRegStatus();
        $bookmark->setData($data);
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
