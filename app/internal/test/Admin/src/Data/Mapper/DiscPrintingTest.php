<?php
namespace AdminTest\Data\Mapper;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Admin\Data\Mapper\DiscPrinting as Sut;
use Zend\Form\Form;

/**
 * Disc Printing Mapper Test
 */
class DiscPrintingTest extends MockeryTestCase
{
    public function testMapFromResultForPrefixes()
    {
        $prefixes = [
            1 => 'AB',
            2 => 'CD'
        ];
        $expected = [
            [
                'value' => 1,
                'label' => 'AB'
            ],
            [
                'value' => 2,
                'label' => 'CD'
            ],
        ];
        $this->assertEquals($expected, Sut::mapFromResultForPrefixes($prefixes));
    }

    /**
     * @dataProvider fromFormProvider
     */
    public function testMapFromForm($params, $expected)
    {
        $this->assertEquals($expected, Sut::mapFromForm($params));
    }

    public function fromFormProvider()
    {
        return [
            [
                [
                    'operator-location' => ['niFlag' => 'Y'],
                    'operator-type' => ['goodsOrPsv' => 'lcat_gv'],
                    'licence-type' => ['licenceType' => 'ltyp_r'],
                    'discs-numbering' => ['startNumber' => 1],
                    'prefix' => ['discSequence' => 2],
                    'discPrefix' =>  'OB',
                    'isSuccessfull' => 1,
                    'endNumber' => 5
                ],
                [
                    'niFlag' => 'Y',
                    'operatorType' => 'lcat_gv',
                    'licenceType' => 'ltyp_r',
                    'startNumber' => 1,
                    'discSequence' => 2,
                    'discPrefix' =>  'OB',
                    'isSuccessfull' => 1,
                    'endNumber' => 5
                 ]
            ],
            [
                [
                    'niFlag' => 'Y',
                    'operatorType' => 'lcat_gv',
                    'licenceType' => 'ltyp_r',
                    'startNumberEntered' => 1,
                    'discSequence' => 2,
                    'discPrefix' =>  'OB',
                    'isSuccessfull' => 1,
                    'endNumber' => 5
                ],
                [
                    'niFlag' => 'Y',
                    'operatorType' => 'lcat_gv',
                    'licenceType' => 'ltyp_r',
                    'startNumber' => 1,
                    'discSequence' => 2,
                    'discPrefix' =>  'OB',
                    'isSuccessfull' => 1,
                    'endNumber' => 5
                ]
            ],
            [
                [],
                [
                    'niFlag' => '',
                    'operatorType' => '',
                    'licenceType' => '',
                    'startNumber' => null,
                    'discSequence' => '',
                    'discPrefix' =>  '',
                    'isSuccessfull' => '',
                    'endNumber' => ''
                ]
            ],
        ];
    }

    public function testMapFromErrors()
    {
        $errors = [
            'startNumber' => ['err_decr' => 'abc'],
            'general' => 'error'
        ];
        $expected = [
            'general' => 'error'
        ];
        $mockForm = m::mock()
            ->shouldReceive('setMessages')
            ->with(['discs-numbering' => ['startNumber' => ['abc']]])
            ->once()
            ->getMock();

        $this->assertEquals($expected, Sut::mapFromErrors($mockForm, $errors));
    }
}
