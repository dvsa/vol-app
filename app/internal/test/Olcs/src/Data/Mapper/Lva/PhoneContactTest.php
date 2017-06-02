<?php

namespace OlcsTest\Data\Mapper\Lva;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Lva\PhoneContact;
use Zend\Form\FormInterface;

/**
 * @covers Olcs\Data\Mapper\Lva\PhoneContact
 */
class PhoneContactTest extends MockeryTestCase
{
    /** @var  array */
    private $formData;

    public function setUp()
    {
        $this->formData = [
            PhoneContact::DETAILS => [
                'id' => 'unit_Id',
                'phoneNumber' => 'unit_PhoneNr',
                'version' => 'unit_Ver',
                'phoneContactType' => RefData::PHONE_TYPE_PRIMARY,
                'contactDetailsId' => 'unit_CdId',
            ],
        ];
    }

    public function testMapFromResult()
    {
        $apiData = [
            'id' => 'unit_Id',
            'phoneNumber' => 'unit_PhoneNr',
            'version' => 'unit_Ver',
            'phoneContactType' => RefData::PHONE_TYPE_PRIMARY,
            'contactDetails' => [
                'id' => 'unit_CdId',
            ],
        ];

        static::assertEquals($this->formData, PhoneContact::mapFromResult($apiData));
    }

    public function testMapFromForm()
    {
        static::assertEquals(
            [
                'id' => 'unit_Id',
                'phoneNumber' => 'unit_PhoneNr',
                'version' => 'unit_Ver',
                'phoneContactType' => RefData::PHONE_TYPE_PRIMARY,
                'contactDetailsId' => 'unit_CdId',
            ],
            PhoneContact::mapFromForm($this->formData)
        );
    }

    public function testMapFromErrorsNull()
    {
        /** @var \Zend\Form\FormInterface $mockForm */
        $mockForm = m::mock(FormInterface::class);

        static::assertEquals([], PhoneContact::mapFromErrors($mockForm, ['messages'=> []]));
    }

    public function testMapFromErrors()
    {
        $errors = [
            'messages' => [
                [
                    'unit_CommonErr',
                ],
                'unit_Field' => [
                    'unit_Field_ErrMsg',
                ],
            ],
        ];

        $mockField = m::mock(\Zend\Form\ElementInterface::class)
            ->shouldReceive('getName')->once()->andReturn('unit_Field')
            ->shouldReceive('setMessages')->once()->with(['unit_Field_ErrMsg'])
            ->getMock();

        $mockField2 = m::mock(\Zend\Form\ElementInterface::class)
            ->shouldReceive('getName')->once()->andReturn('unit_Field2')
            ->getMock();

        /** @var \Zend\Form\Form $mockForm */
        $mockForm = m::mock(FormInterface::class)
            ->shouldReceive('get')->once()->with(PhoneContact::DETAILS)->andReturn([$mockField, $mockField2])
            ->getMock();

        $actual = PhoneContact::mapFromErrors($mockForm, $errors);

        static::assertEquals(
            [
                ['unit_CommonErr'],
            ],
            $actual
        );
    }
}
