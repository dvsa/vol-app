<?php

declare(strict_types=1);

namespace OlcsTest\Mvc\Controller\ParameterProvider;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Mvc\Controller\ParameterProvider\PreviousPiHearingData;
use Laminas\Mvc\Controller\Plugin\Params;

/**
 * Class PreviousPiHearingDataTest
 * @package OlcsTest\Mvc\Controller\ParameterProvider
 */
class PreviousPiHearingDataTest extends TestCase
{
    /**
     *
     * @param $venueOther
     * @param $venue
     * @param $outputVenue
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideParametersProvider')]
    public function testProvideParameters(mixed $venueOther, mixed $venue, mixed $outputVenue): void
    {
        $piId = 44;
        $presidingTc = 22;
        $presidedByRole = 33;
        $witnesses = 5;
        $details = 'details';

        $pi = [
            'id' => $piId,
            'piHearings' => [
                0 => [

                ],
                1 => [
                    'venue' => [
                        'id' => $venue,
                    ],
                    'venueOther' => $venueOther,
                    'presidingTc' => [
                        'id' => $presidingTc
                    ],
                    'presidedByRole' => [
                        'id' => $presidedByRole
                    ],
                    'witnesses' => $witnesses,
                    'details' => $details
                ]
            ]
        ];

        $expected = [
            'pi' => $piId,
            'venue' => $outputVenue,
            'venueOther' => $venueOther,
            'presidingTc' => $presidingTc,
            'presidedByRole' => $presidedByRole,
            'witnesses' => $witnesses,
            'details' => $details
        ];

        $sut = new PreviousPiHearingData($pi);
        $this->assertEquals($expected, $sut->provideParameters());
    }

    /**
     * @return array
     */
    public static function provideParametersProvider(): array
    {
        return [
            ['other venue', 11, 'other'],
            ['', 11, 11]
        ];
    }
}
