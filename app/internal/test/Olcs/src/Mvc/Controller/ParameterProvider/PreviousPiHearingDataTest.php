<?php


namespace OlcsTest\Mvc\Controller\ParameterProvider;

use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Mockery as m;
use Olcs\Mvc\Controller\ParameterProvider\PreviousPiHearingData;
use Zend\Mvc\Controller\Plugin\Params;

/**
 * Class PreviousPiHearingDataTest
 * @package OlcsTest\Mvc\Controller\ParameterProvider
 */
class PreviousPiHearingDataTest extends TestCase
{

    /**
     * @dataProvider provideParametersProvider
     *
     * @param $piVenueOther
     * @param $piVenue
     * @param $outputPiVenue
     */
    public function testProvideParameters($piVenueOther, $piVenue, $outputPiVenue)
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
                    'piVenue' => [
                        'id' => $piVenue,
                    ],
                    'piVenueOther' => $piVenueOther,
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
            'piVenue' => $outputPiVenue,
            'piVenueOther' => $piVenueOther,
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
    public function provideParametersProvider()
    {
        return [
            ['other venue', 11, 'other'],
            ['', 11, 11]
        ];
    }
}
