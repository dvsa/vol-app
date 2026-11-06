<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\FeatureToggle;
use Common\Form\Elements\Types\Address as AddressFieldset;
use Common\Service\Cqrs\Query\QuerySender;
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

    private m\MockInterface $querySender;

    #[\Override]
    public function setUp(): void
    {
        $this->querySender = m::mock(QuerySender::class);
        $this->classArgs = [$this->querySender];
        parent::setUp();
    }

    private function captureIs(bool $on): void
    {
        $this->querySender->shouldReceive('featuresEnabled')
            ->with([FeatureToggle::SMS_NUMBER_CAPTURE])
            ->once()
            ->andReturn($on);
    }

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
        $this->captureIs(true);
        $this->expectSmsHintRemoved($mockForm);

        // the establishment address labels and consultant address ids are covered by the common Addresses test
        $mockForm->expects('has')->with('establishment_address')->andReturn(false);
        $consultantAddress = m::mock(AddressFieldset::class);
        $consultantAddress->expects('useNamesAsIds')->withNoArgs()->andReturnNull();
        $mockForm->expects('get')->with('consultantAddress')->andReturn($consultantAddress);

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
        $this->captureIs(true);

        $mockForm->shouldReceive('get')->with('form-actions')->andReturn(
            m::mock(\Laminas\Form\FieldsetInterface::class)
                ->shouldReceive('get')->andReturn(
                    m::mock(\Laminas\Form\ElementInterface::class)->shouldReceive('setLabel')->getMock()
                )
                ->getMock()
        );

        $this->expectSmsHintRemoved($mockForm);
        $mockForm->expects('has')->with('establishment_address')->andReturn(false);
        $consultantAddress = m::mock(AddressFieldset::class);
        $consultantAddress->expects('useNamesAsIds')->withNoArgs()->andReturnNull();
        $mockForm->expects('get')->with('consultantAddress')->andReturn($consultantAddress);

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

    public function testSmsNumberHiddenUntilCaptureIsOn(): void
    {
        $mockForm = m::mock(\Laminas\Form\Form::class);
        $this->formHelper->shouldReceive('createForm')->andReturn($mockForm);
        $this->captureIs(false);

        $mockForm->shouldReceive('get')->with('form-actions')->andReturn(
            m::mock(\Laminas\Form\FieldsetInterface::class)
                ->shouldReceive('get')->andReturn(
                    m::mock(\Laminas\Form\ElementInterface::class)->shouldReceive('setLabel')->getMock()
                )
                ->getMock()
        );
        $mockForm->expects('has')->with('establishment_address')->andReturn(false);
        $consultantAddress = m::mock(AddressFieldset::class);
        $consultantAddress->expects('useNamesAsIds')->withNoArgs()->andReturnNull();
        $mockForm->expects('get')->with('consultantAddress')->andReturn($consultantAddress);
        $mockForm->shouldReceive('get')->with('contact')->never();

        $this->formHelper->shouldReceive('removeFieldList')
            ->once()
            ->with($mockForm, 'contact', ['phone_sms', 'phone_sms_id', 'phone_sms_version']);

        $optional = m::mock(Input::class)->shouldReceive('setRequired')->with(false)->andReturnSelf()->getMock();
        $contact = m::mock(InputFilter::class);
        $contact->shouldReceive('get')->with('email')->andReturn($optional);
        $contact->shouldReceive('get')->with('phone_primary')->andReturn($optional);
        $inputFilter = m::mock(InputFilter::class);
        $inputFilter->shouldReceive('get')->with('contact')->andReturn($contact);
        $mockForm->shouldReceive('getInputFilter')->andReturn($inputFilter);

        $this->sut->getForm(
            ['typeOfLicence' => ['licenceType' => 'ltyp_sn'], 'corrPhoneContacts' => ['unit_PhoneContacts']]
        );
    }

    /** The SMS hint speaks to operators, so caseworkers don't see it. */
    private function expectSmsHintRemoved(m\MockInterface $mockForm): void
    {
        $sms = m::mock(\Laminas\Form\ElementInterface::class);
        $sms->shouldReceive('setOption')->with('hint', null)->once()->andReturnSelf();
        $contact = m::mock(\Laminas\Form\FieldsetInterface::class);
        $contact->shouldReceive('has')->with('phone_sms')->andReturn(true);
        $contact->shouldReceive('get')->with('phone_sms')->andReturn($sms);
        $mockForm->shouldReceive('get')->with('contact')->andReturn($contact);
    }
}
