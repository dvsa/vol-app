<?php

namespace OlcsTest\FormService\Form\Lva;

use Common\RefData;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use OlcsTest\FormService\Form\Lva\Stub\VariationOverviewSubmissionStub;

/**
 * @covers Olcs\FormService\Form\Lva\VariationOverviewSubmission
 */
class VariationOverviewSubmissionTest extends MockeryTestCase
{
    /** @var  VariationOverviewSubmissionStub */
    private $sut;

    /** @var  m\MockInterface | \Zend\Form\FormInterface */
    private $mockForm;
    /** @var  m\MockInterface | \Common\Service\Helper\FormHelperService */
    private $mockFormHlp;

    public function setUp()
    {
        $this->mockForm = m::mock(\Zend\Form\FormInterface::class);

        $this->mockFormHlp = m::mock(\Common\Service\Helper\FormHelperService::class);

        $this->sut = new VariationOverviewSubmissionStub();
        $this->sut->setFormHelper($this->mockFormHlp);
    }

    /**
     * @dataProvider  dpTestAlterForm
     */
    public function testAlterFormDescription($section, $expect)
    {
        //  mock expected parent call
        $this->mockParentCall();

        //  mock expected
        $this->mockFormHlp->shouldReceive('createForm')->andReturn($this->mockForm);

        $this->mockForm
            ->shouldReceive('get')
            ->with('description')
            ->once()
            ->andReturn(
                m::mock(\Zend\Form\ElementInterface::class)
                    ->shouldReceive('setLabel')->once()->with($expect)
                    ->getMock()
            );

        //  call
        $this->sut->getForm(
            [
                'outstandingFeeTotal' => -1,
                'disableCardPayments' => false,
            ],
            [
                'sections' => [
                    $section,
                ],
                'isReadyToSubmit' => false,
            ]
        );
    }

    public function dpTestAlterForm()
    {
        return [
            [
                'section' => [
                    'status' => 'unit_ExpectedStatus',
                ],
                'expect' => 'variation.overview.submission.desc.notchanged',
            ],
            [
                'section' => [
                    'status' => RefData::VARIATION_STATUS_REQUIRES_ATTENTION,
                ],
                'expect' => 'variation.overview.submission.desc.req-attention',
            ],
            [
                'section' => [
                    'status' => RefData::VARIATION_STATUS_UPDATED,
                ],
                'expect' => 'variation.overview.submission.desc.must-submit',
            ],
        ];
    }

    private function mockParentCall()
    {
        //  mock expected parent call
        $mockElm = m::mock(\Zend\Form\Element::class)->makePartial();

        $this->mockForm
            ->shouldReceive('get')->with('submitPay')->zeroOrMoreTimes()->andReturn($mockElm)
            ->shouldReceive('setAttribute')->zeroOrMoreTimes();

        $this->mockFormHlp
            ->shouldReceive('remove')->with($this->mockForm, 'amount')->once()
            ->shouldReceive('disableElement')->zeroOrMoreTimes();
    }
}
