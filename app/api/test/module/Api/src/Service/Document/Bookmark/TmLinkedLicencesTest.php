<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TmLinkedLicences;

/**
 * Tm Linked Licences test
 */
class TmLinkedLicencesTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TmLinkedLicences();
        $query = $bookmark->getQuery(['transportManager' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new TmLinkedLicences();
        $bookmark->setData(
            [
                'result' => [
                    [
                        'licence' => [
                            'licNo' => 'licNo1',
                            'organisation' => ['name' => 'Org name'],
                        ]
                    ],
                    [
                        'licence' => [
                            'licNo' => 'licNo2',
                            'organisation' => ['name' => 'Org name'],
                        ]
                    ],
                    [
                        'licence' => [
                            'licNo' => 'licNo3',
                            'organisation' => ['MISSING' => 'Org name'],
                        ]
                    ],
                ]
            ]
        );

        $this->assertEquals(
            "licNo1: Org name\nlicNo2: Org name",
            $bookmark->render()
        );
    }
}
