<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Laminas\I18n\View\Helper\Translate;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use OlcsTest\FormService\Form\Lva\Stub\ApplicationOverviewSubmissionStub;

/**
 * @covers Olcs\FormService\Form\Lva\ApplicationOverviewSubmission
 */
class ApplicationOverviewSubmissionTest extends MockeryTestCase
{
    /** @var  ApplicationOverviewSubmissionStub */
    private $sut;

    /** @var  m\MockInterface | \Laminas\Form\FormInterface */
    private $mockForm;
    /** @var  m\MockInterface | \Common\Service\Helper\FormHelperService */
    private $mockFormHlp;

    public function setUp(): void
    {
        $this->mockForm = m::mock(\Laminas\Form\FormInterface::class);

        $this->mockFormHlp = m::mock(\Common\Service\Helper\FormHelperService::class);

        $mockTranslationHelper = m::mock(Translate::class);

        $this->sut = new ApplicationOverviewSubmissionStub($mockTranslationHelper, $this->mockFormHlp);
    }

    public function testAlterFormReadySubmit(): void
    {
        $data = [
            'outstandingFeeTotal' => -1,
            'disableCardPayments' => false,
        ];
        $params = [
            'actionUrl' => 'unit_ActionUrl',
            'isReadyToSubmit' => true,
        ];

        //  mock expected parent call
        $this->mockParentCall();

        //  call’
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

        //  mock expected parent call
        $this->mockParentCall();

        //  expect
        $mockDescElm = m::mock(\Laminas\Form\ElementInterface::class)
            ->shouldReceive('setLabel')->once()->with('application.overview.submission.desc.notcomplete')
            ->getMock();

        $this->mockForm
            ->shouldReceive('get')->with('description')->once()->andReturn($mockDescElm);

        $this->mockFormHlp
            ->shouldReceive('remove')->with($this->mockForm, 'description')->never();

        //  call
        $this->sut->alterForm($this->mockForm, $data, $params);
    }

    private function mockParentCall(): void
    {
        //  mock expected parent call
        $mockElm = m::mock(\Laminas\Form\Element::class)->makePartial();

        $this->mockForm
            ->shouldReceive('get')->with('submitPay')->zeroOrMoreTimes()->andReturn($mockElm)
            ->shouldReceive('setAttribute')->zeroOrMoreTimes();

        $this->mockFormHlp
            ->shouldReceive('remove')->with($this->mockForm, 'amount')->once()
            ->shouldReceive('remove')->zeroOrMoreTimes();
    }
}
