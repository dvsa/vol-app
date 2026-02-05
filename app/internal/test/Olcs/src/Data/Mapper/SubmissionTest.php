<?php

namespace OlcsTest\Data\Mapper;

use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Submission as Sut;

class SubmissionTest extends MockeryTestCase
{
    /**
     *
     * @param $expected
     * @param $inData
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('assignedDateDataProvider')]
    public function testReadOnlyFields($expected, $inData)
    {
        $actual = Sut::mapFromResult($inData);
        $this->assertEquals($expected, $actual['readOnlyFields']);
    }

    public function testDefaultDateSet()
    {

        $inData = [

            ['assignedDate' => null, 'informationCompleteDate' => null]
        ];
        $actual = Sut::mapFromResult($inData);
        $this->assertEquals((new \DateTime('now'))->format('Y-m-d'), $actual['fields']['assignedDate']);
    }

    public static function assignedDateDataProvider()
    {

        return [
            [
                [
                    'assignedDate',
                    'informationCompleteDate'
                ],
                ['assignedDate' => null, 'informationCompleteDate' => 'TEST']
            ]
        ];
    }
}
