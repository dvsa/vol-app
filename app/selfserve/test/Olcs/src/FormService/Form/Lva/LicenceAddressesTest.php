<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\RefData;
use Laminas\Form\ElementInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Addresses\LicenceAddresses;
use Laminas\Form\Form;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;

final class LicenceAddressesTest extends MockeryTestCase
{
    protected $sut;

    protected $formHelper;

    #[\Override]
    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();

        $this->sut = new LicenceAddresses($this->formHelper);
    }

    public function testGetForm(): void
    {
        $formActions = m::mock(ElementInterface::class);
        $formActions->shouldReceive('get')
            ->with('save')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('setAttribute')
                ->with('class', 'govuk-button')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $mockForm = m::mock(Form::class);
        $this->mockSmsRequired($mockForm);
        $mockForm->shouldReceive('has')->with('form-actions')->andReturn(true);
        $mockForm->shouldReceive('get')->with('form-actions')->andReturn($formActions);

        $this->formHelper
            ->shouldReceive('createForm')
            ->with('Lva\Addresses')
            ->andReturn($mockForm)
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->once()
            ->getMock();

        $this->sut->getForm(
            ['typeOfLicence' => ['licenceType' => RefData::LICENCE_TYPE_STANDARD_NATIONAL]]
        );
    }

    private function mockSmsRequired(Form $mockForm): void
    {
        $smsInput = m::mock(Input::class);
        $smsInput->shouldReceive('setRequired')->once()->with(true)->andReturnSelf();
        $contact = m::mock(InputFilter::class);
        $contact->shouldReceive('get')->with('phone_sms')->once()->andReturn($smsInput);
        $inputFilter = m::mock(InputFilter::class);
        $inputFilter->shouldReceive('get')->with('contact')->once()->andReturn($contact);
        $mockForm->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);
    }
}
