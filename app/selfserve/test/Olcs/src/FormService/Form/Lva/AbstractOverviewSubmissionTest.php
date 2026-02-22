<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\Form\Element\DynamicMultiCheckbox;
use Common\Form\Element\DynamicRadio;
use Common\Form\Element\DynamicSelect;
use Common\Service\Translator\TranslationLoader;
use Laminas\I18n\Translator\LoaderPluginManager;
use Laminas\I18n\View\Helper\Translate;
use Laminas\Mvc\Service\ServiceManagerConfig;
use Laminas\ServiceManager\ServiceManager;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\FormService\Form\Lva\Stub\AbstractOverviewSubmissionStub;

/**
 * @covers Olcs\FormService\Form\Lva\AbstractOverviewSubmission
 */
class AbstractOverviewSubmissionTest extends MockeryTestCase
{
    /** @var  AbstractOverviewSubmissionStub */
    private $sut;

    /** @var  m\MockInterface | \Laminas\Form\FormInterface */
    private $mockForm;
    /** @var  m\MockInterface | \Common\Service\Helper\FormHelperService */
    private $mockFormHlp;

    private $mockTranslationHelper;


    public function setUp(): void
    {
        $this->mockForm = m::mock(\Laminas\Form\FormInterface::class);

        $this->mockTranslationHelper = m::mock(Translate::class);
        $this->mockTranslationHelper
            ->shouldReceive('translateReplace')
            ->andReturnUsing(
                fn($text, $params) => '_TRLTD_' . $text . '[' . implode('|', $params) . ']'
            );

        $this->mockFormHlp = m::mock(\Common\Service\Helper\FormHelperService::class)->makePartial();

        $this->sut = new AbstractOverviewSubmissionStub($this->mockTranslationHelper, $this->mockFormHlp);
    }

    public function testGetForm(): void
    {
        $data = ['data'];
        $params = [
            'sections' => ['unit_Sections'],
        ];

        $this->mockFormHlp
            ->shouldReceive('createForm')->once()->with('Lva\PaymentSubmission')->andReturn($this->mockForm);

        /** @var AbstractOverviewSubmissionStub | m\MockInterface $sut */
        $sut = m::mock(AbstractOverviewSubmissionStub::class . '[alterForm]', [$this->mockTranslationHelper, $this->mockFormHlp])
            ->shouldAllowMockingProtectedMethods()
            ->shouldReceive('alterForm')->once()->with($this->mockForm, $data, $params)
            ->getMock();

        static::assertSame($this->mockForm, $sut->getForm($data, $params));
    }

    public function testAlterFormReadySubmitWithFee(): void
    {
        $data = [
            'outstandingFeeTotal' => 999,
            'disableCardPayments' => true,
        ];
        $params = [
            'actionUrl' => 'unit_ActionUrl',
            'isReadyToSubmit' => true,
        ];

        //  expect
        $mockAmountElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setValue')->once()->with('_TRLTD_application.payment-submission.amount.value[999.00]')
            ->getMock();

        $mocksubmitPayElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setLabel')->once()->with('submit-application.button')
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')->once()->with('amount')->andReturn($mockAmountElm)
            ->shouldReceive('get')->once()->with('submitPay')->andReturn($mocksubmitPayElm)
            ->shouldReceive('setAttribute')->once()->with('action', 'unit_ActionUrl');

        //  call
        $this->sut->alterForm($this->mockForm, $data, $params);
    }

    public function testAlterFormNotReadyNoFee(): void
    {
        $data = [
            'outstandingFeeTotal' => -1,
            'disableCardPayments' => false,
        ];
        $params = [
            'isReadyToSubmit' => false,
        ];

        //  expect
        $mocksubmitPayElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setLabel')->once()->with('submit-application.button')
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')->never()->with('amount')
            ->shouldReceive('get')->once()->with('submitPay')->andReturn($mocksubmitPayElm);

        $this->mockFormHlp
            ->shouldReceive('remove')->once()->with($this->mockForm, 'amount')
            ->shouldReceive('remove')->once()->with($this->mockForm, 'submitPay');

        //  call
        $this->sut->alterForm($this->mockForm, $data, $params);
    }
}
