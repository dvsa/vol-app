<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\Addresses;

use Common\Service\Helper\FormHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Addresses\ApplicationAddresses;
use Laminas\Form\Form;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Application Addresses Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class ApplicationAddressesTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var ApplicationAddresses
     */
    protected $sut;

    protected $fh;

    #[\Override]
    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->sut = new ApplicationAddresses($this->fh);
    }

    public function testAlterForm(): void
    {
        $mockForm = m::mock(Form::class);
        $this->mockSmsRequired($mockForm);
        $this->mockAlterButtons($mockForm, $this->fh);

        $this->fh->shouldReceive('createForm')
            ->once()
            ->with('Lva\Addresses')
            ->andReturn($mockForm)
            ->shouldReceive('remove')
            ->with($mockForm, 'establishment')
            ->andReturnSelf()
            ->once()
            ->shouldReceive('remove')
            ->with($mockForm, 'establishment_address')
            ->andReturnSelf()
            ->once()
            ->getMock();

        $form = $this->sut->getForm(['typeOfLicence' => ['licenceType' => 'foo']]);

        $this->assertSame($mockForm, $form);
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
