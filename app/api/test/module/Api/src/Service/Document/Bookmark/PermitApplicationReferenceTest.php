<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\PermitApplicationReference as Sut;

/**
 * PermitApplicationReference bookmark test
 */
class PermitApplicationReferenceTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['irhpPermit' => 123]);

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
                    'irhpPermitApplication' => [
                        'relatedApplication' => [
                            'applicationRef' => 'OB1234567/1',
                        ]
                    ]
                ],
                'OB1234567/1'
            ],
            [
                [],
                ''
            ],
        ];
    }
}
