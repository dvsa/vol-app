<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\EventHistory;

/**
 * @covert \Olcs\Data\Mapper\EventHistory
 */
class EventHistoryTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $data = [
            'eventData' => 'bar',
            'eventDatetime' => '2015-03-19T13:37:36+0000',
            'user' => [
                'contactDetails' => [
                    'person' => [
                        'forename' => 'cake',
                        'familyName' => 'baz',
                    ],
                ],
            ],
            'eventHistoryDetails' => [
                [
                    'newValue' => '2017-06-05 04:03:00',
                    'oldValue' => '2016-05-04 03:02:00',
                    'name' => 'open_date',
                ],
                [
                    'name' => 'unit_NOT_DATE',
                ],
                [
                    'newValue' => '2017-06-05 04:03:00',
                    'oldValue' => '',
                    'name' => 'closed_date',
                ],
                [
                    'newValue' => null,
                    'oldValue' => '2016-05-04 03:02:00',
                    'name' => 'deleted_date',
                ],
            ],
            'eventHistoryType' => [
                'description' => 'foo',
            ],
        ];
        $expected = [
            'readOnlyData' => [
                'details' => 'foo',
                'info' => 'bar',
                'date' => '19/03/2015 13:37',
                'by' => 'cake baz',
            ],
            'eventHistoryDetails' => [
                [
                    'newValue' => '05/06/2017 04:03',
                    'oldValue' => '04/05/2016 03:02',
                    'name' => 'open_date',
                ],
                [
                    'name' => 'unit_NOT_DATE',
                ],
                [
                    'newValue' => '05/06/2017 04:03',
                    'oldValue' => '',
                    'name' => 'closed_date',
                ],
                [
                    'newValue' => null,
                    'oldValue' => '04/05/2016 03:02',
                    'name' => 'deleted_date',
                ],
            ],
        ];
        $this->assertEquals($expected, EventHistory::mapFromResult($data));
    }
}
