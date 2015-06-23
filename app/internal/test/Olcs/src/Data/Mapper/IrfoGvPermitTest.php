<?php
namespace OlcsTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\IrfoGvPermit as Sut;
use Zend\Form\FormInterface;

/**
 * IrfoGvPermit Mapper Test
 */
class IrfoGvPermitTest extends MockeryTestCase
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
                [],
                ['fields' => []]
            ],
            // edit
            [
                [
                    'id' => 987,
                    'organisation' => ['id' => 100],
                    'createdOn' => '2015-05-05',
                    'expiryDate' => '2015-05-20',
                    'irfoPermitStatus' => 'other_status',
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'organisation' => 100,
                        'irfoPermitStatus' => 'other_status',
                        'createdOn' => '2015-05-05',
                        'expiryDate' => '2015-05-20',
                        'idHtml' => 987,
                        'createdOnHtml' => '05/05/2015',
                        'expiryDateHtml' => '20/05/2015',
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
