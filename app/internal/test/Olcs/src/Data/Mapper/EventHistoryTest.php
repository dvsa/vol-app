<?php

/**
 * Event history mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\EventHistory as Sut;
use Zend\Form\FormInterface;

/**
 * Event history mapper test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
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
                        'familyName' => 'baz'
                    ]
                ]
            ],
            'eventHistoryDetails' => 'qux',
            'eventHistoryType' => [
                'description' => 'foo'
            ]
        ];
        $expected = [
            'readOnlyData' => [
                'details' => 'foo',
                'info' => 'bar',
                'date' => '13:37, 19/03/15',
                'by' => 'cake baz'
            ],
            'eventHistoryDetails' => 'qux'
        ];
        $this->assertEquals($expected, Sut::mapFromResult($data));
    }
}
