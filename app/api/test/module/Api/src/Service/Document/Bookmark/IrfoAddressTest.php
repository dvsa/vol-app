<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\IrfoAddress as Sut;

/**
 * IrfoAddress bookmark test
 */
class IrfoAddressTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new Sut();
        $query = $bookmark->getQuery(['organisation' => 123]);

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
                    'irfoContactDetails' => [
                        'address' => [
                            'addressLine1' => 'Line 1',
                            'addressLine2' => 'Line 2',
                            'addressLine3' => 'Line 3',
                            'addressLine4' => 'Line 4',
                            'town' => 'Leeds',
                            'postcode' => 'LS9 6NF',
                            'countryCode' => [
                                'countryDesc' => 'United Kingdom'
                            ]
                        ]
                    ]
                ],
                'Line 1, Line 2, Line 3, Line 4, Leeds, LS9 6NF, United Kingdom'
            ],
            [
                [],
                ''
            ],
        ];
    }
}
