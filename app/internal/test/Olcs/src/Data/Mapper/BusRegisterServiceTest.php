<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\BusRegisterService as Sut;
use Laminas\Form\FormInterface;

/**
 * BusRegisterService Mapper Test
 */
class BusRegisterServiceTest extends MockeryTestCase
{
    /**
    * @dataProvider mapFromResultDataProvider
    *
    * @param $inData
    * @param $expected
    */
    public function testMapFromResult($inData, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public function mapFromResultDataProvider()
    {
        return [
            // edit
            [
                [
                    'id' => 987,
                    'version' => 1,
                    'timetableAcceptable' => 'Y',
                    'mapSupplied' => 'Y',
                    'routeDescription' => 'test 1',
                    'trcConditionChecked' => 'Y',
                    'trcNotes' => 'test 2',
                    'variationReasons' => [
                        ['id' => 101, 'description' => 'vr1'],
                        ['id' => 102, 'description' => 'vr2'],
                    ],
                    'busNoticePeriod' => [
                        'id' => 1,
                    ]
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'version' => 1,
                        'timetableAcceptable' => 'Y',
                        'mapSupplied' => 'Y',
                        'routeDescription' => 'test 1',
                        'trcConditionChecked' => 'Y',
                        'trcNotes' => 'test 2',
                        'variationReasons' => [
                            ['id' => 101, 'description' => 'vr1'],
                            ['id' => 102, 'description' => 'vr2'],
                        ],
                        'variationReasonsHtml' => 'vr1, vr2',
                        'busNoticePeriod' => 1
                    ],
                    'timetable' => [
                        'timetableAcceptable' => 'Y',
                        'mapSupplied' => 'Y',
                        'routeDescription' => 'test 1',
                    ],
                    'conditions' => [
                        'trcConditionChecked' => 'Y',
                        'trcNotes' => 'test 2',
                    ],
                ]
            ]
        ];
    }

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

    public function mapFromFormDataProvider()
    {
        return [
            [
                [
                    'fields' => [
                        'id' => 987,
                        'version' => 1,
                    ],
                    'timetable' => [
                        'timetableAcceptable' => 'Y',
                        'mapSupplied' => 'Y',
                        'routeDescription' => 'test 1',
                    ],
                    'conditions' => [
                        'trcConditionChecked' => 'Y',
                        'trcNotes' => 'test 2',
                    ],
                ],
                [
                    'id' => 987,
                    'version' => 1,
                    'timetableAcceptable' => 'Y',
                    'mapSupplied' => 'Y',
                    'routeDescription' => 'test 1',
                    'trcConditionChecked' => 'Y',
                    'trcNotes' => 'test 2',
                    'opNotifiedLaPte' => 'N',
                    'laShortNote' => 'N',
                ]
            ],
        ];
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
