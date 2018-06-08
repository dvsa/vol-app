<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Submission as Sut;

class SubmissionTest extends MockeryTestCase
{
    /**
     * @dataProvider assignedDateDataProvider
     *
     * @param $expected
     * @param $inData
     */
    public function testAssignedDateReadOnly($expected, $inData)
    {
        $actual = Sut::mapFromResult($inData);
        $this->assertEquals($expected, $actual);
    }

    public function assignedDateDataProvider()
    {
        return [
            [['fields' => ['assignedDate' => 'TEST'], 'readOnlyFields' => ['assignedDate']], ['assignedDate' => 'TEST']]
        ];
    }
}
