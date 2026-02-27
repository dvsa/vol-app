<?php

declare(strict_types=1);

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
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromResultDataProvider')]
    public function testMapFromResult(mixed $inData, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromResult($inData));
    }

    public static function mapFromResultDataProvider(): array
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
     *
     * @param $inData
     * @param $expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('mapFromFormDataProvider')]
    public function testMapFromForm(mixed $inData, mixed $expected): void
    {
        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public static function mapFromFormDataProvider(): array
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

    public function testMapFromErrors(): void
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
