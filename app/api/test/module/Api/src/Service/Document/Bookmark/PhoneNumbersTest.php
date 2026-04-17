<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Entity\ContactDetails\PhoneContact;
use Dvsa\Olcs\Api\Service\Document\Bookmark\PhoneNumbers;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Phone Numbers bookmark test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class PhoneNumbersTest extends MockeryTestCase
{
    public function testGetQuery(): void
    {
        $bookmark = new PhoneNumbers();
        $query = $bookmark->getQuery(['licence' => 123]);

        $this->assertInstanceOf(\Dvsa\Olcs\Transfer\Query\QueryInterface::class, $query);
    }

    public function testRenderWithNoPhoneNumbers(): void
    {
        $bookmark = new PhoneNumbers();
        $bookmark->setData([]);

        $this->assertEquals(
            '',
            $bookmark->render()
        );
    }

    public function testRenderWithPhoneNumbers(): void
    {
        $bookmark = m::mock(\Dvsa\Olcs\Api\Service\Document\Bookmark\PhoneNumbers::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_2CELL_TABLE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'correspondenceCd' => [
                    'phoneContacts' => [
                        [
                            'phoneNumber' => '22222',
                            'phoneContactType' => [
                                'id' => PhoneContact::TYPE_SECONDARY,
                                'description' => 'Secondary'
                            ]
                        ],
                        [
                            'phoneNumber' => '11111',
                            'phoneContactType' => [
                                'id' => PhoneContact::TYPE_PRIMARY,
                                'description' => 'Primary'
                            ]
                        ],
                    ]
                ]
            ]
        );

        $header1 = [
            'BOOKMARK1' => 'Phone number(s)',
            'BOOKMARK2' => ''
        ];
        $header2 = [
            'BOOKMARK1' => 'Type of contact number',
            'BOOKMARK2' => 'Number'
        ];
        $row1 = [
            'BOOKMARK1' => 'Primary',
            'BOOKMARK2' => '11111'
        ];
        $row2 = [
            'BOOKMARK1' => 'Secondary',
            'BOOKMARK2' => '22222'
        ];
        $row3 = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => ''
        ];
        $row4 = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => ''
        ];
        $emptyRow = [
            'BOOKMARK1' => '',
            'BOOKMARK2' => ''
        ];

        $mockParser = m::mock(\Dvsa\Olcs\Api\Service\Document\Parser\RtfParser::class)
            ->shouldReceive('replace')
            ->with('snippet', $header1)
            ->andReturn('header1|')
            ->once()
            ->shouldReceive('replace')
            ->with('snippet', $header2)
            ->andReturn('header2|')
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

        $rendered = 'header1|header2|row1|row2|row3|row4|emptyrow|';
        $this->assertEquals(
            $rendered,
            $bookmark->render()
        );
    }
}
