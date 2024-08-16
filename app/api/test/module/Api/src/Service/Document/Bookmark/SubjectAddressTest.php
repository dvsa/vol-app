<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\SubjectAddress;

/**
 * Subject Address test
 */
class SubjectAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new SubjectAddress();

        $this->assertTrue(is_null($bookmark->getQuery([])));

        $query = $bookmark->getQuery(['opposition' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    /**
     * @dataProvider renderDataProvider
     */
    public function testRender($data, $expected)
    {
        $bookmark = new SubjectAddress();
        $bookmark->setData($data);

        $this->assertEquals($expected, $bookmark->render());
    }

    public function renderDataProvider()
    {
        return [
            // no results
            [
                [],
                ''
            ],
            // opposer with contact address
            [
                [
                    'opposer' => [
                        'contactDetails' => [
                            'address' => [
                                'addressLine1' => 'Line 1',
                                'addressLine2' => 'Line 2'
                            ]
                        ]
                    ]
                ],
                "Line 1\nLine 2"
            ],
        ];
    }
}
