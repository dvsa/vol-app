<?php

namespace OlcsTest\Data\Mapper\Lva;

use Common\RefData;
use Laminas\Form\Fieldset;
use Laminas\Stdlib\PriorityList;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Data\Mapper\Lva\PhoneContact;
use Laminas\Form\FormInterface;

/**
 * @covers Olcs\Data\Mapper\Lva\PhoneContact
 */
class PhoneContactTest extends MockeryTestCase
{
    /** @var  array */
    private $formData;

    public function setUp(): void
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
        /** @var \Laminas\Form\FormInterface $mockForm */
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

        $mockField = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('getName')->once()->andReturn('unit_Field')
            ->shouldReceive('setMessages')->once()->with(['unit_Field_ErrMsg'])
            ->getMock();

        $mockField2 = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('getName')->once()->andReturn('unit_Field2')
            ->getMock();

        $fieldset = m::mock(Fieldset::class);
        $elements = new PriorityList();
        $elements->insert(1, $mockField);
        $elements->insert(2, $mockField2);
        $fieldset->shouldReceive('getIterator')->andReturn($elements);

        /** @var \Laminas\Form\Form $mockForm */
        $mockForm = m::mock(FormInterface::class)
            ->shouldReceive('get')->once()->with(PhoneContact::DETAILS)->andReturn($fieldset)
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
