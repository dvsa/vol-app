<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Mockery as m;
use Olcs\FormService\Form\Lva\Addresses;
use Laminas\InputFilter\InputFilter;
use Laminas\InputFilter\Input;

#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\FormService\Form\Lva\Addresses::class)]
final class AddressesTest extends AbstractLvaFormServiceTestCase
{
    protected $classToTest = Addresses::class;

    /** @var  \Olcs\FormService\Form\Lva\Addresses */
    protected $sut;

    #[\Override]
    public function testGetForm(): void
    {
        // Mocks
        $mockForm = m::mock(\Laminas\Form\Form::class);

        $this->formHelper
            ->shouldReceive('createForm')->andReturn($mockForm);

        //  check set label
        $mockElmSaveBtn = m::mock(\Laminas\Form\ElementInterface::class);
        $mockElmSaveBtn->shouldReceive('setLabel')->with('internal.save.button')->once();

        $mockFsActions = m::mock(\Laminas\Form\FieldsetInterface::class);
        $mockFsActions->shouldReceive('get')->once()->andReturn($mockElmSaveBtn);

        $mockForm->shouldReceive('get')->with('form-actions')->once()->andReturn($mockFsActions);

        //  check change email setting
        $mockInputFilter = m::mock(InputFilter::class)
            ->shouldReceive('get')
            ->with('contact')
            ->once()
            ->andReturn(
                m::mock()
                    ->shouldReceive('get')
                    ->with('email')
                    ->andReturn(
                        m::mock(Input::class)
                            ->shouldReceive('setRequired')->once()->with(false)->andReturnSelf()
                            ->getMock()
                    )
                    ->shouldReceive('get')
                    ->with('phone_primary')
                    ->andReturn(
                        m::mock(Input::class)
                            ->shouldReceive('setRequired')->once()->with(false)->andReturnSelf()
                            ->getMock()
                    )
                    ->getMock()
            )
            ->getMock();

        $mockForm->shouldReceive('getInputFilter')->once()->andReturn($mockInputFilter);

        //  call
        $form = $this->sut->getForm(
            [
                'typeOfLicence' => [
                    'licenceType' => 'ltyp_sn',
                ],
                'corrPhoneContacts' => [
                    'unit_PhoneContacts',
                ],
            ]
        );

        $this->assertSame($mockForm, $form);
    }

    public function testSmsNumberOptional(): void
    {
        $mockForm = m::mock(\Laminas\Form\Form::class);
        $this->formHelper->shouldReceive('createForm')->andReturn($mockForm);

        $mockForm->shouldReceive('get')->with('form-actions')->andReturn(
            m::mock(\Laminas\Form\FieldsetInterface::class)
                ->shouldReceive('get')->andReturn(
                    m::mock(\Laminas\Form\ElementInterface::class)->shouldReceive('setLabel')->getMock()
                )
                ->getMock()
        );

        $optional = m::mock(Input::class)->shouldReceive('setRequired')->with(false)->andReturnSelf()->getMock();
        $contact = m::mock(InputFilter::class);
        $contact->shouldReceive('get')->with('email')->andReturn($optional);
        $contact->shouldReceive('get')->with('phone_primary')->andReturn($optional);
        $contact->shouldReceive('get')->with('phone_sms')->never();
        $inputFilter = m::mock(InputFilter::class);
        $inputFilter->shouldReceive('get')->with('contact')->andReturn($contact);
        $mockForm->shouldReceive('getInputFilter')->andReturn($inputFilter);

        $this->sut->getForm(
            ['typeOfLicence' => ['licenceType' => 'ltyp_sn'], 'corrPhoneContacts' => ['unit_PhoneContacts']]
        );
    }
}
