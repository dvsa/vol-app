<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\PiHearing as Sut;
use Zend\Form\FormInterface;

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
        $piVenueOther = 'pi venue other';
        $adjournedReason = 'cancelled reason';
        $adjournedDate = '2015-12-25';
        $cancelledReason = 'cancelled reason';
        $cancelledDate = '2015-12-26';
        $details = 'details';

        return [
            [
                [
                    'fields' => [
                        'piVenue' => 'other',
                        'piVenueOther' => $piVenueOther,
                        'isCancelled' => 'Y',
                        'cancelledReason' => $cancelledReason,
                        'cancelledDate' => $cancelledDate,
                        'isAdjourned' => 'Y',
                        'adjournedReason' => $adjournedReason,
                        'adjournedDate' => $adjournedDate,
                        'details' => $details
                    ],
                    'form-actions' => [
                        'publish' => true
                    ]
                ],
                [
                    'piVenue' => null,
                    'piVenueOther' => $piVenueOther,
                    'isCancelled' => 'Y',
                    'cancelledReason' => $cancelledReason,
                    'cancelledDate' => $cancelledDate,
                    'isAdjourned' => 'Y',
                    'adjournedReason' => $adjournedReason,
                    'adjournedDate' => $adjournedDate,
                    'details' => $details,
                    'publish' => 'Y',
                    'text2' => $details
                ]
            ],
            [
                [
                    'fields' => [
                        'piVenue' => 1,
                        'piVenueOther' => $piVenueOther,
                        'isCancelled' => 'N',
                        'cancelledReason' => $cancelledReason,
                        'cancelledDate' => $cancelledDate,
                        'isAdjourned' => 'N',
                        'adjournedReason' => $adjournedReason,
                        'adjournedDate' => $adjournedDate,
                        'details' => $details
                    ],
                ],
                [
                    'piVenue' => 1,
                    'piVenueOther' => null,
                    'isCancelled' => 'N',
                    'cancelledReason' => null,
                    'cancelledDate' => null,
                    'isAdjourned' => 'N',
                    'adjournedReason' => null,
                    'adjournedDate' => null,
                    'details' => $details,
                    'publish' => 'N'
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
        $piVenueOther = 'pi venue other';
        $otherFieldId = 99;
        $witnesses = 88;

        return [
            [
                [],
                [
                    'fields' => [
                        'witnesses' => 0
                    ]
                ]
            ],
            [
                [
                    'piVenue' => 1,
                    'piVenueOther' => $piVenueOther,
                    'otherField' => [
                        'id' => $otherFieldId
                    ],
                    'witnesses' => $witnesses
                ],
                [
                    'fields' => [
                        'piVenue' => 'other',
                        'piVenueOther' => $piVenueOther,
                        'otherField' => $otherFieldId,
                        'witnesses' => $witnesses
                    ]
                ]
            ],
            [
                [
                    'piVenue' => 1,
                    'piVenueOther' => $piVenueOther,
                    'otherField' => [
                        'id' => $otherFieldId
                    ],
                    'witnesses' => null
                ],
                [
                    'fields' => [
                        'piVenue' => 'other',
                        'piVenueOther' => $piVenueOther,
                        'otherField' => $otherFieldId,
                        'witnesses' => 0
                    ]
                ]
            ]
        ];
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
