<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\IrfoPsvFrequency as Sut;

/**
 * @covers Dvsa\Olcs\Api\Service\Document\Bookmark\IrfoPsvFrequency
 */
class IrfoPsvFrequencyTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['irfoPsvAuth' => 123]);

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
