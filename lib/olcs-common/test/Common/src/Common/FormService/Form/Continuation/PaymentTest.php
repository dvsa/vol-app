<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\Service\Helper\GuidanceHelperService;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Continuation\Payment;
use Common\Form\Model\Form\Continuation\Payment as PaymentForm;
use Common\Service\Helper\FormHelperService;
use Common\FormService\FormServiceManager;

/**
 * Licence payment form service test
 */
class PaymentTest extends MockeryTestCase
{
    /** @var PaymentForm */
    protected $sut;

    /** @var  m\MockInterface */
    private $formHelper;

    protected $guidance;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->guidance = m::mock(GuidanceHelperService::class);

        $this->sut = new Payment($this->formHelper, $this->guidance);
    }

    public function testGetForm(): void
    {
        $form = m::mock(PaymentForm::class)
            ->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock()
                    ->shouldReceive('remove')
                    ->with('pay')
                    ->once()
                    ->shouldReceive('get')
                    ->with('cancel')
                    ->andReturn(
                        m::mock()
                        ->shouldReceive('setLabel')
                        ->with('back-to-fees')
                        ->once()
                        ->shouldReceive('setAttribute')
                        ->andReturn('class', 'govuk-button govuk-button--secondary')
                        ->once()
                        ->getMock()
                    )
                    ->once()
                    ->getMock()
            )
            ->twice()
            ->getMock();

        $this->formHelper
            ->shouldReceive('createForm')
            ->with(PaymentForm::class)
            ->andReturn($form)
            ->once()
            ->getMock();

        $this->guidance
            ->shouldReceive('append')
            ->with('selfserve-card-payments-disabled')
            ->once()
            ->getMock();

        $data = [
            'disableCardPayments' => true
        ];

        $this->assertEquals($form, $this->sut->getForm($data));
    }
}
