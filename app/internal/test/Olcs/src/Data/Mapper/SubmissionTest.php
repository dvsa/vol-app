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
    public function testReadOnlyFields($expected, $inData)
    {
        $actual = Sut::mapFromResult($inData);
        var_dump($actual);
        $this->assertEquals($expected, $actual);
    }

    public function assignedDateDataProvider()
    {
        return [
            [['fields' => ['assignedDate' => 'TEST'], 'readOnlyFields' => ['assignedDate','informationCompleteDate']], ['assignedDate' => 'TEST','informationCompleteDate' =>'TEST']]
        ];
    }
}
