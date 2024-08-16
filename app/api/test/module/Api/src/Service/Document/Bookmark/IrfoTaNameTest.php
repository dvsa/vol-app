<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\IrfoTaName as Sut;

/**
 * IrfoTaName test
 */
class IrfoTaNameTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['organisation' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    /**
     * @dataProvider dpRenderValidDataProvider
     */
    public function testRender($results, $expected)
    {
        $bookmark = new Sut();
        $bookmark->setData($results);

        $this->assertEquals($expected, $bookmark->render());
    }

    public function dpRenderValidDataProvider()
    {
        return [
            [
                [
                    'tradingNames' => [
                        [
                            'name' => 'Trading Name 1',
                        ],
                        [
                            'name' => 'Trading Name 2',
                            'licence' => [
                                'id' => 10
                            ]
                        ],
                        [
                            'name' => 'Trading Name 3',
                        ],
                        [
                            'name' => 'Trading Name 4',
                        ],
                    ]
                ],
                'T/A: Trading Name 1 Trading Name 3 Trading Name 4',
            ],
            [
                [
                    'tradingNames' => [
                        [
                            'name' => 'Trading Name 2',
                            'licence' => [
                                'id' => 10
                            ]
                        ],
                    ]
                ],
                '',
            ],
            [
                [
                    'tradingNames' => []
                ],
                '',
            ]
        ];
    }
}
