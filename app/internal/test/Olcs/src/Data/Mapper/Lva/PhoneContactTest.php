<?php

namespace OlcsTest\Data\Mapper\Lva;

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
                'phoneContactType' => 'phone_t_tel',
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
            'phoneContactType' => 'phone_t_tel',
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
                'phoneContactType' => 'phone_t_tel',
                'contactDetailsId' => 'unit_CdId',
            ],
            PhoneContact::mapFromForm($this->formData)
        );
    }

    public function testMapFromErrors()
    {
        $errors = [
            'messages' => [
                'field' => [
                    'invalid',
                ],
            ],
        ];

        /** @var \Zend\Form\Form $mockForm */
        $mockForm = m::mock(FormInterface::class)
            ->shouldReceive('setMessages')
            ->once()
            ->with(
                [
                    PhoneContact::DETAILS => [
                        'field' => [
                            'invalid',
                        ],
                    ],
                ]
            )
            ->getMock();

        $actual = PhoneContact::mapFromErrors($mockForm, $errors);

        static::assertEquals($errors, $actual);
    }
}
