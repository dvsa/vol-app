<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\LicencePartners;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Licence Partners bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class LicencePartnersTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new LicencePartners();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoLicencePartners(): void
    {
        $bookmark = new LicencePartners();
        $bookmark->setData([]);

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithLicencePartners(): void
    {
        $bookmark = m::mock(\Dvsa\Olcs\Api\Service\Document\Bookmark\LicencePartners::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_2CELL_TABLE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'organisation' => [
                    'organisationPersons' => [
                        [
                            'person' => [
                                'forename' => 'First',
                                'familyName' => 'Person',
                                'birthDate' => '1973-02-01',
                            ]
                        ],
                        [
                            'person' => [
                                'forename' => 'Second',
                                'familyName' => 'Person',
                                'birthDate' => '1972-03-02',
                            ]
                        ]
                    ],
                ]
            ]
        );

        $header = [
            'BOOKMARK1' => 'List of partners/directors (please enter full name if not shown)',
            'BOOKMARK2' => 'Date of birth (please complete if not shown)'
        ];
        $row1 = [
            'BOOKMARK1' => 'First Person',
            'BOOKMARK2' => '01/02/1973'
        ];
        $row2 = [
            'BOOKMARK1' => 'Second Person',
            'BOOKMARK2' => '02/03/1972'
        ];
        $emptyRow = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => ''
        ];

        $mockParser = m::mock(\Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class)
            ->shouldReceive('replace')
            ->with('snippet', $header)
            ->andReturn('header|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row1)
            ->andReturn('row1|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row2)
            ->andReturn('row2|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $emptyRow)
            ->andReturn('emptyrow|')
            ->once()
            ->getMock();

        $bookmark->setParser($mockParser);

        $rendered = 'header|row1|row2|emptyrow|';
        $this->assertEquals(
            $rendered,
            $bookmark->render()
        );
    }
}
