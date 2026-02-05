<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Service\Document\Bookmark\InterimUnlinkedTm;

/**
 * Interim Unlinked TM test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class InterimUnlinkedTmTest extends \PHPUnit\Framework\TestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new InterimUnlinkedTm();
        $query = $bookmark->getQuery(['application' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithRestrictedLicence(): void
    {
        $bookmark = new InterimUnlinkedTm();
        $bookmark->setData(
            [
                'licenceType' => [
                    'id' => Licence::LICENCE_TYPE_RESTRICTED
                ]
            ]
        );

        $this->assertEquals('N/A', $bookmark->render());
    }

    public function testRenderWithNoTms(): void
    {
        $bookmark = new InterimUnlinkedTm();
        $bookmark->setData(
            [
                'licenceType' => [
                    'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                ],
                'transportManagers' => []
            ]
        );

        $this->assertEquals('None added as part of this application', $bookmark->render());
    }

    public function testRenderWithTms(): void
    {
        $bookmark = new InterimUnlinkedTm();
        $bookmark->setData(
            [
                'licenceType' => [
                    'id' => Licence::LICENCE_TYPE_STANDARD_NATIONAL
                ],
                'transportManagers' => [
                    [
                        'transportManager' => [
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'A',
                                    'familyName' => 'Person'
                                ]
                            ]
                        ]
                    ], [
                        'transportManager' => [
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'B',
                                    'familyName' => 'Person'
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        );

        $this->assertEquals("A Person\nB Person", $bookmark->render());
    }
}
