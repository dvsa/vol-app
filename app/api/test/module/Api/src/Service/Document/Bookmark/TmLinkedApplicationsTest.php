<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TmLinkedApplications;

/**
 * Tm Linked Applications test
 */
class TmLinkedApplicationsTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TmLinkedApplications();
        $query = $bookmark->getQuery(['transportManager' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRender(): void
    {
        $bookmark = new TmLinkedApplications();
        $bookmark->setData(
            [
                'tmApplications' => [
                    [
                        'application' => [
                            'id' => 1,
                            'licence' => [
                                'licNo' => 'licNo1',
                                'organisation' => ['name' => 'Org name']
                            ]
                        ]
                    ],
                    [
                        'application' => [
                            'id' => 2,
                            'licence' => [
                                'licNo' => 'licNo2',
                                'organisation' => ['name' => 'Org name']
                            ]
                        ]
                    ],
                ]
            ]
        );

        $this->assertEquals(
            "licNo1/1: Org name\nlicNo2/2: Org name",
            $bookmark->render()
        );
    }
}
