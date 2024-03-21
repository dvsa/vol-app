<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\CpmsReport as Sut;
use Laminas\Form\FormInterface;

/**
 * CpmsReport Mapper Test
 */
class CpmsReportTest extends MockeryTestCase
{
    public function testMapFromResult()
    {
        $in = ['foo'];
        $this->assertSame($in, Sut::mapFromResult($in));
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
                    'reportOptions' => [
                        'reportCode' => 'FOO',
                        'startDate' => '2015-10-07',
                        'endDate' => '2015-10-08',
                    ]
                ],
                [
                    'reportCode' => 'FOO',
                    'start' => '2015-10-07',
                    'end' => '2015-10-08',
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
