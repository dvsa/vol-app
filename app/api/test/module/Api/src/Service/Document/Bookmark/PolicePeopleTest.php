<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Document\Bookmark;

use Dvsa\Olcs\Api\Domain\Query\Bookmark\PolicePeopleBundle;
use Dvsa\Olcs\Api\Service\Document\Bookmark\PolicePeople;
use Dvsa\Olcs\Api\Service\Document\Parser\RtfParser;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Police People bookmark test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PolicePeopleTest extends MockeryTestCase
{
    /**
     * Tests getQuery
     */
    public function testGetQuery(): void
    {
        $bookmark = new PolicePeople();
        $query = $bookmark->getQuery(['id' => 123]);

        $this->assertInstanceOf(PolicePeopleBundle::class, $query);
    }

    /**
     * Tests rendering when we have no data
     */
    public function testRenderWithNoData(): void
    {
        $bookmark = new PolicePeople();
        $bookmark->setData([]);

        $this->assertEquals(
            PolicePeople::HEADING_LINE . PolicePeople::NO_ENTRIES,
            $bookmark->render()
        );
    }

    /**
     * Tests data renders correctly
     *
     *
     * @param array $licence
     * @param string $expectedLicNo
     * @param string $pubType
     * @param string $expectedSection
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('renderWithDataProvider')]
    public function testRenderWithData(mixed $licence, mixed $expectedLicNo, mixed $pubType, mixed $expectedSection): void
    {
        $forename1 = 'forename 1';
        $familyName1 = 'family name 1';
        $birthDate1 = '2015-12-25';
        $birthDate1Formatted = '25/12/2015';

        $forename2 = 'forename 2';
        $familyName2 = 'family name 2';

        $adSection = '2.1';
        $npSection = '3.2';

        $bookmark = m::mock(PolicePeople::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('getSnippet')
            ->with('CHECKLIST_4CELL_TABLE')
            ->andReturn('snippet')
            ->once()
            ->getMock();

        $bookmark->setData(
            [
                'publicationLinks' => [
                    0 => [
                        'licence' => $licence,
                        'publication' => [
                            'pubType' => $pubType,
                        ],
                        'publicationSection' => [
                            'adSection' => $adSection,
                            'npSection' => $npSection
                        ],
                        'policeDatas' => [
                            0 => [
                                'forename' => $forename1,
                                'familyName' => $familyName1,
                                'birthDate' => $birthDate1
                            ],
                            1 => [
                                'forename' => $forename2,
                                'familyName' => $familyName2,
                                'birthDate' => null
                            ]
                        ]
                    ],
                ]
            ]
        );

        $tableHeader = [
            'BOOKMARK1' => PolicePeople::BOLD_START . ' Name ' . PolicePeople::BOLD_END,
            'BOOKMARK2' => PolicePeople::BOLD_START . ' D.O.B. ' . PolicePeople::BOLD_END,
            'BOOKMARK3' => PolicePeople::BOLD_START . ' Licence no. ' . PolicePeople::BOLD_END,
            'BOOKMARK4' => PolicePeople::BOLD_START . ' Section ' . PolicePeople::BOLD_END,
        ];
        $row1 = [
            'BOOKMARK1' => $forename1 . ' ' . $familyName1,
            'BOOKMARK2' => $birthDate1Formatted,
            'BOOKMARK3' => $expectedLicNo,
            'BOOKMARK4' => $expectedSection,
        ];
        $row2 = [
            'BOOKMARK1' => $forename2 . ' ' . $familyName2,
            'BOOKMARK2' => 'Unknown',
            'BOOKMARK3' => $expectedLicNo,
            'BOOKMARK4' => $expectedSection,
        ];

        $mockParser = m::mock(RtfParser::class);
        $mockParser->shouldReceive('replace')
            ->with('snippet', $tableHeader)
            ->andReturn('tableheader|')
            ->once();
        $mockParser->shouldReceive('replace')
            ->with('snippet', $row1)
            ->andReturn('row|')
            ->once();
        $mockParser->shouldReceive('replace')
            ->with('snippet', $row2)
            ->andReturn('row2|')
            ->once();

        $bookmark->setParser($mockParser);

        $rendered = PolicePeople::HEADING_LINE . 'tableheader|row|row2|';
        $this->assertEquals(
            $rendered,
            $bookmark->render()
        );
    }

    public static function renderWithDataProvider(): array
    {
        $licNo = 'OB1234567';

        return [
            [
                ['licNo' => $licNo],
                $licNo,
                'A&D',
                '2.1'
            ],
            [
                [],
                null,
                'N&P',
                '3.2'
            ]
        ];
    }
}
