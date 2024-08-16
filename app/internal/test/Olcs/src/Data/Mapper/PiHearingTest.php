<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\PiHearing as Sut;
use Laminas\Form\FormInterface;

/**
 * Pi Hearing Mapper Test
 */
class PiHearingTest extends MockeryTestCase
{
    /**
     * @dataProvider mapFromFormDataProvider
     *
     * @param $inData
     * @param $expected
     */
    public function testMapFromForm($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    /**
     * Data provider for mapFromForm
     *
     * @return array
     */
    public function mapFromFormDataProvider()
    {
        $venueOther = 'pi venue other';
        $adjournedReason = 'cancelled reason';
        $adjournedDate = '2015-12-25';
        $cancelledReason = 'cancelled reason';
        $cancelledDate = '2015-12-26';
        $details = 'details';

        return [
            [
                [
                    'fields' => [
                        'venue' => 'other',
                        'venueOther' => $venueOther,
                        'isCancelled' => 'Y',
                        'cancelledReason' => $cancelledReason,
                        'cancelledDate' => $cancelledDate,
                        'isAdjourned' => 'Y',
                        'adjournedReason' => $adjournedReason,
                        'adjournedDate' => $adjournedDate,
                        'details' => $details,
                        'isFullDay' => 'not-set',
                    ],
                    'form-actions' => [
                        'publish' => true
                    ]
                ],
                [
                    'venue' => null,
                    'venueOther' => $venueOther,
                    'isCancelled' => 'Y',
                    'cancelledReason' => $cancelledReason,
                    'cancelledDate' => $cancelledDate,
                    'isAdjourned' => 'Y',
                    'adjournedReason' => $adjournedReason,
                    'adjournedDate' => $adjournedDate,
                    'details' => $details,
                    'publish' => 'Y',
                    'text2' => $details,
                    'isFullDay' => 'not-set',
                ]
            ],
            [
                [
                    'fields' => [
                        'venue' => 1,
                        'venueOther' => $venueOther,
                        'isCancelled' => 'N',
                        'cancelledReason' => $cancelledReason,
                        'cancelledDate' => $cancelledDate,
                        'isAdjourned' => 'N',
                        'adjournedReason' => $adjournedReason,
                        'adjournedDate' => $adjournedDate,
                        'details' => $details,
                        'isFullDay' => 'Y',
                    ],
                ],
                [
                    'venue' => 1,
                    'venueOther' => null,
                    'isCancelled' => 'N',
                    'cancelledReason' => null,
                    'cancelledDate' => null,
                    'isAdjourned' => 'N',
                    'adjournedReason' => null,
                    'adjournedDate' => null,
                    'details' => $details,
                    'publish' => 'N',
                    'isFullDay' => 'Y',
                ]
            ]
        ];
    }

    /**
     * @dataProvider mapFromResultDataProvider
     *
     * @param $inData
     * @param $expected
     *
     * Tests mapFromResult
     */
    public function testMapFromResult($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    /**
     * Data provider for mapFromResult
     *
     * @return array
     */
    public function mapFromResultDataProvider()
    {
        $venueOther = 'pi venue other';
        $otherFieldId = 99;
        $witnesses = 88;
        $drivers = 10;

        return [
            [
                [],
                [
                    'fields' => [
                        'witnesses' => 0,
                        'isFullDay' => 'not-set',
                        'drivers' => 0,
                    ]
                ]
            ],
            [
                [
                    'venue' => 1,
                    'venueOther' => $venueOther,
                    'otherField' => [
                        'id' => $otherFieldId
                    ],
                    'witnesses' => $witnesses,
                    'isFullDay' => 'Y',
                    'drivers'   => $drivers,
                ],
                [
                    'fields' => [
                        'venue' => 'other',
                        'venueOther' => $venueOther,
                        'otherField' => $otherFieldId,
                        'witnesses' => $witnesses,
                        'isFullDay' => 'Y',
                        'drivers'   => $drivers,
                    ]
                ]
            ],
            [
                [
                    'venue' => 1,
                    'venueOther' => $venueOther,
                    'otherField' => [
                        'id' => $otherFieldId
                    ],
                    'witnesses' => null,
                    'isFullDay' => 'N',
                    'drivers'   => null,
                ],
                [
                    'fields' => [
                        'venue' => 'other',
                        'venueOther' => $venueOther,
                        'otherField' => $otherFieldId,
                        'witnesses' => 0,
                        'isFullDay' => 'N',
                        'drivers'   => 0,
                    ]
                ]
            ]
        ];
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data', 'messages' => []];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }

    public function testMapFromErrorsHearDateBeforePi()
    {
        $mockForm = m::mock(FormInterface::class);
        $mockForm->shouldReceive('get->get->setMessages')
            ->with(['Hearing date must be after or the same as the PI agreed date 26/05/2017'])
            ->once();
        $errors = ['messages' => ['HEARING_DATE_BEFORE_PI' => '2017-05-26']];

        $this->assertEquals(['messages' => []], Sut::mapFromErrors($mockForm, $errors));
    }
}
