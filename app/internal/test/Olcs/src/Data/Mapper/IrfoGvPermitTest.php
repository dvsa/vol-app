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
        $now = new \DateTime();

        return [
            // add
            [
                [
                    'now' => $now,
                ],
                [
                    'fields' => [
                        'yearRequired' => $now->format('Y'),
                        'irfoPermitStatusHtml' => 'Pending',
                        'inForceDate' => $now,
                    ]
                ]
            ],
            // edit
            [
                [
                    'id' => 987,
                    'organisation' => ['id' => 100],
                    'yearRequired' => '2010',
                    'irfoPermitStatus' => [
                        'id' => 'other_status',
                        'description' => 'other status',
                    ],
                    'createdOn' => '2015-05-05',
                    'inForceDate' => '2015-05-20',
                ],
                [
                    'fields' => [
                        'id' => 987,
                        'organisation' => 100,
                        'yearRequired' => '2010',
                        'irfoPermitStatus' => 'other_status',
                        'irfoPermitStatusHtml' => 'other status',
                        'createdOn' => '2015-05-05',
                        'createdOnHtml' => '05/05/2015',
                        'inForceDate' => '2015-05-20',
                        'idHtml' => 987,
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
