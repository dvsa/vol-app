<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Service\Document\Bookmark\TransportManagers;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * TransportManagers bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagersTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new TransportManagers();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoTransportManagers(): void
    {
        $bookmark = new TransportManagers();
        $bookmark->setData([]);

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithTransportManagers(): void
    {
        $bookmark = m::mock(\Dvsa\Olcs\Api\Service\Document\Bookmark\TransportManagers::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_2CELL_TABLE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'tmLicences' => [
                    [
                      'transportManager' => [
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'B',
                                    'familyName' => 'Surname',
                                    'birthDate' => '1971-03-02'
                                ]
                            ]
                        ]
                    ],
                    [
                      'transportManager' => [
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'A',
                                    'familyName' => 'Surname',
                                    'birthDate' => '1972-02-01'
                                ]
                            ]
                        ]
                    ],
                    [
                      'transportManager' => [
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'D',
                                    'familyName' => 'Surname',
                                    'birthDate' => '1970-04-01'
                                ]
                            ]
                        ]
                    ],
                    [
                      'transportManager' => [
                            'homeCd' => [
                                'person' => [
                                    'forename' => 'C',
                                    'familyName' => 'Surname',
                                    'birthDate' => '1969-04-01'
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        );

        $header = [
            'BOOKMARK1' => 'List of transport managers (only applicable to standard licences)',
            'BOOKMARK2' => 'Date of birth (please complete if not shown)'
        ];
        $row1 = [
            'BOOKMARK1' => 'A Surname',
            'BOOKMARK2' => '01/02/1972'
        ];
        $row2 = [
            'BOOKMARK1' => 'B Surname',
            'BOOKMARK2' => '02/03/1971'
        ];
        $row3 = [
            'BOOKMARK1' => 'C Surname',
            'BOOKMARK2' => '01/04/1969'
        ];
        $row4 = [
            'BOOKMARK1' => 'D Surname',
            'BOOKMARK2' => '01/04/1970'
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
            ->with('snippet', $row3)
            ->andReturn('row3|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $row4)
            ->andReturn('row4|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $emptyRow)
            ->andReturn('emptyrow|')
            ->once()
            ->getMock();

        $bookmark->setParser($mockParser);

        $rendered = 'header|row1|row2|row3|row4|emptyrow|';
        $this->assertEquals(
            $rendered,
            $bookmark->render()
        );
    }
}
