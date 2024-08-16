<?php

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\UnlinkedTm;

/**
 * @covers \Dvsa\Olcs\Api\Service\Document\Bookmark\UnlinkedTm
 */
class UnlinkedTmTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery()
    {
        $bookmark = new UnlinkedTm();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function dpRenderValidDataProvider()
    {
        return [
            [
                "Testy McTest",
                [
                    'tmLicences' => [
                        0 => [
                            'transportManager' => [
                                'homeCd' => [
                                    'person' => [
                                        'forename' => 'Testy',
                                        'familyName' => 'McTest',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                "Lorem Ipsum\nTesty McTest",
                [
                    'tmLicences' => [
                        0 => [
                            'transportManager' => [
                                'homeCd' => [
                                    'person' => [
                                        'forename' => 'Lorem',
                                        'familyName' => 'Ipsum',
                                    ],
                                ],
                            ],
                        ],
                        1 => [
                            'transportManager' => [
                                'homeCd' => [
                                    'person' => [
                                        'forename' => 'Testy',
                                        'familyName' => 'McTest',
                                    ],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
            [
                UnlinkedTm::TM_NA,
                [
                    'tmLicences' => [],
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_RESTRICTED,
                    ],
                ],
            ],
            [
                UnlinkedTm::TM_BE_NOMINATED,
                [
                    'tmLicences' => [],
                    'licenceType' => [
                        'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL,
                    ],
                ],
            ],
        ];
    }

    /**
     * @dataProvider dpRenderValidDataProvider
     */
    public function testRender($expected, $results)
    {
        $bookmark = new UnlinkedTm();
        $bookmark->setData($results);

        $this->assertEquals($expected, $bookmark->render());
    }
}
