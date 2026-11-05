<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva\Addresses;

use Common\Service\Helper\FormHelperService;
use Common\FeatureToggle;
use Common\Service\Cqrs\Query\QuerySender;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\FormService\Form\Lva\Addresses\VariationAddresses;
use Laminas\Form\Form;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use OlcsTest\FormService\Form\Lva\Traits\ButtonsAlterations;

/**
 * Variation Addresses Form Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class VariationAddressesTest extends MockeryTestCase
{
    use ButtonsAlterations;

    /**
     * @var VariationAddresses
     */
    protected $sut;

    protected $fh;

    private m\MockInterface $querySender;

    #[\Override]
    public function setUp(): void
    {
        $this->fh = m::mock(FormHelperService::class)->makePartial();
        $this->querySender = m::mock(QuerySender::class);
        $this->sut = new VariationAddresses($this->fh, $this->querySender);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpCaptureToggle')]
    public function testAlterForm(bool $captureOn): void
    {
        $mockForm = m::mock(Form::class);
        $this->mockSmsRequired($mockForm, $captureOn);

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
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->cancel')
            ->andReturnSelf()
            ->once()
            ->getMock();

        $form = $this->sut->getForm(['typeOfLicence' => ['licenceType' => 'foo']]);

        $this->assertSame($mockForm, $form);
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
    }
}
