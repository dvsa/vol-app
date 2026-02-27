<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter\Format;

use Dvsa\Olcs\Api\Service\Nr\Filter\Format\SiDates;
use PHPUnit\Framework\TestCase as TestCase;

class SiDatesTest extends TestCase
{
    #[\PHPUnit\Framework\Attributes\DataProvider('filterProvider')]
    public function testFilter(array $inputPenaltyDates, array $expectedPenaltyDates): void
    {

        $input = [
            'infringementDate' => '2015-12-24',
            'imposedErrus' => [0 => $inputPenaltyDates]
        ];
        $expectedOutput = [
            'infringementDate' => new \DateTime('2015-12-24 00:00:00'),
            'imposedErrus' => [0 => $expectedPenaltyDates]
        ];

        $sut = new SiDates();
        $this->assertEquals($expectedOutput, $sut->filter($input));
    }

    /**
     * data provider for testFilterProvider
     */
    public static function filterProvider(): array
    {
        return [
            [
                ['finalDecisionDate' => '2015-12-25'],
                [
                    'finalDecisionDate' => new \DateTime('2015-12-25 00:00:00'),
                    'startDate' => null,
                    'endDate' => null
                ]
            ],
            [
                [
                    'finalDecisionDate' => '2015-12-25',
                    'startDate' => '2015-12-26',
                    'endDate' => '2015-12-27'
                ],
                [
                    'finalDecisionDate' => new \DateTime('2015-12-25 00:00:00'),
                    'startDate' => new \DateTime('2015-12-26 00:00:00'),
                    'endDate' => new \DateTime('2015-12-27 00:00:00')
                ]
            ],
        ];
    }
}
