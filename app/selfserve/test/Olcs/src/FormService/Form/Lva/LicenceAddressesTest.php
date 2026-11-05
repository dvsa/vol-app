<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\RefData;
use Laminas\Form\ElementInterface;
use Common\FeatureToggle;
use Common\Service\Cqrs\Query\QuerySender;
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

    private m\MockInterface $querySender;

    #[\Override]
    public function setUp(): void
    {
        $this->formHelper = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();

        $this->querySender = m::mock(QuerySender::class);
        $this->sut = new LicenceAddresses($this->formHelper, $this->querySender);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCaptureToggle')]
    public function testGetForm(bool $captureOn): void
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
        $this->mockSmsRequired($mockForm, $captureOn);
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

    public static function dpCaptureToggle(): \Iterator
    {
        yield 'capture on' => [true];
        yield 'capture off' => [false];
    }

    private function mockSmsRequired(Form $mockForm, bool $captureOn): void
    {
        $this->querySender->shouldReceive('featuresEnabled')
            ->with([FeatureToggle::SMS_NUMBER_CAPTURE])
            ->once()
            ->andReturn($captureOn);

        if (!$captureOn) {
            // field stays optional
            $mockForm->shouldReceive('getInputFilter')->never();
            return;
        }

        $smsInput = m::mock(Input::class);
        $smsInput->shouldReceive('setRequired')->once()->with(true)->andReturnSelf();
        $contact = m::mock(InputFilter::class);
        $contact->shouldReceive('get')->with('phone_sms')->once()->andReturn($smsInput);
        $inputFilter = m::mock(InputFilter::class);
        $inputFilter->shouldReceive('get')->with('contact')->once()->andReturn($contact);
        $mockForm->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        // Required, so the label drops "(optional)".
        $smsElement = m::mock(\Laminas\Form\Element::class);
        $smsElement->shouldReceive('setLabel')->once()->with('contact-number-sms');
        $contactFieldset = m::mock(\Laminas\Form\Fieldset::class);
        $contactFieldset->shouldReceive('get')->with('phone_sms')->once()->andReturn($smsElement);
        $mockForm->shouldReceive('get')->with('contact')->once()->andReturn($contactFieldset);
    }
}
