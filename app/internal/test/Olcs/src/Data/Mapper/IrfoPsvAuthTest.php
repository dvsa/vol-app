<?php

namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\IrfoPsvAuth as Sut;
use Laminas\Form\FormInterface;

/**
 * IrfoPsvAuth Mapper Test
 */
class IrfoPsvAuthTest extends MockeryTestCase
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
            // add
            [
                [
                    'isGrantable' => false,
                    'isRefusable' => false,
                    'isWithdrawable' => false,
                ],
                [
                    'fields' => [
                        'copiesIssued' => 0,
                        'copiesIssuedHtml' => 0,
                        'copiesIssuedTotal' => 0,
                        'copiesIssuedTotalHtml' => 0,
                        'copiesRequired' => 0,
                        'copiesRequiredTotal' => 0,
                        'copiesRequiredNonChargeable' => 0,
                        'isGrantable' => false,
                        'isRefusable' => false,
                        'isWithdrawable' => false
                    ],
                ]
            ],
            // edit
            [
                [
                    'id' => 987,
                    'organisation' => ['id' => 100],
                    'createdOn' => '2015-05-05',
                    'status' => 'other_status',
                    'copiesIssued' => 1,
                    'copiesIssuedTotal' => 11,
                    'copiesRequired' => 3,
                    'copiesRequiredTotal' => 33,
                    'isGrantable' => false,
                    'isRefusable' => false,
                    'isWithdrawable' => false
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'organisation' => 100,
                        'status' => 'other_status',
                        'createdOn' => '2015-05-05',
                        'createdOnHtml' => '05/05/2015',
                        'copiesIssued' => 1,
                        'copiesIssuedHtml' => 1,
                        'copiesIssuedTotal' => 11,
                        'copiesIssuedTotalHtml' => 11,
                        'copiesRequired' => 3,
                        'copiesRequiredTotal' => 33,
                        'copiesRequiredNonChargeable' => 30,
                        'isGrantable' => false,
                        'isRefusable' => false,
                        'isWithdrawable' => false
                    ],
                ]
            ]
        ];
    }

    public function testMapFromForm()
    {
        $inData = ['fields' => ['field' => 'data']];
        $expected = ['field' => 'data'];

        $this->assertEquals($expected, Sut::mapFromForm($inData));
    }

    public function testMapFromErrors()
    {
        $mockForm = m::mock(FormInterface::class);
        $errors = ['field' => 'data'];

        $this->assertEquals($errors, Sut::mapFromErrors($mockForm, $errors));
    }
}
