<?php

namespace Dvsa\OlcsTest\Api\Service\Nr\Filter\Format;

use Dvsa\Olcs\Api\Service\Nr\Filter\Format\SiDates;
use PHPUnit\Framework\TestCase as TestCase;

/**
 * Class SiDatesTest
 * @package Dvsa\OlcsTest\Api\Service\NrFilter\Format
 */
class SiDatesTest extends TestCase
{
    /**
     * Tests the filter
     *
     * @dataProvider filterProvider
     * @param $inputPenaltyDates
     * @param $expectedPenaltyDates
     */
    public function testFilter($inputPenaltyDates, $expectedPenaltyDates)
    {

        $input = [
            'checkDate' => '2015-12-23',
            'infringementDate' => '2015-12-24',
            'imposedErrus' => [0 => $inputPenaltyDates]
        ];
        $expectedOutput = [
            'checkDate' => new \DateTime('2015-12-23 00:00:00'),
            'infringementDate' => new \DateTime('2015-12-24 00:00:00'),
            'imposedErrus' => [0 => $expectedPenaltyDates]
        ];

        $sut = new SiDates();
        $this->assertEquals($expectedOutput, $sut->filter($input));
    }

    /**
     * data provider for testFilterProvider
     */
    public function filterProvider()
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
