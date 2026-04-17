<?php

declare(strict_types=1);

namespace OlcsTest\FormService\Form\Lva;

use Common\RefData;
use Laminas\I18n\View\Helper\Translate;
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

    /** @var  m\MockInterface | \Laminas\Form\FormInterface */
    private $mockForm;
    /** @var  m\MockInterface | \Common\Service\Helper\FormHelperService */
    private $mockFormHlp;

    public function setUp(): void
    {
        $this->mockForm = m::mock(\Laminas\Form\FormInterface::class);

        $this->mockFormHlp = m::mock(\Common\Service\Helper\FormHelperService::class);

        $mockTranslationHelper = m::mock(Translate::class);

        $this->sut = new VariationOverviewSubmissionStub($mockTranslationHelper, $this->mockFormHlp);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestAlterForm')]
    public function testAlterFormDescription(array $section, string $expect): void
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
                m::mock(\Laminas\Form\ElementInterface::class)
                    ->shouldReceive('setLabel')->once()->with($expect)
                    ->getMock()
            );

        $this->mockFormHlp->shouldReceive('remove')
            ->with($this->mockForm, 'submitPay')
            ->once()
            ->getMock();

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

    /**
     * @return ((int|string)[]|string)[][]
     *
     * @psalm-return list{array{section: array{status: 'unit_ExpectedStatus'}, expect: 'variation.overview.submission.desc.notchanged'}, array{section: array{status: 1}, expect: 'variation.overview.submission.desc.req-attention'}, array{section: array{status: 2}, expect: 'variation.overview.submission.desc.must-submit'}}
     */
    public static function dpTestAlterForm(): array
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

    private function mockParentCall(): void
    {
        //  mock expected parent call
        $mockElm = m::mock(\Laminas\Form\Element::class)->makePartial();

        $this->mockForm
            ->shouldReceive('get')->with('submitPay')->zeroOrMoreTimes()->andReturn($mockElm)
            ->shouldReceive('setAttribute')->zeroOrMoreTimes();

        $this->mockFormHlp
            ->shouldReceive('remove')->with($this->mockForm, 'amount')->once()
            ->shouldReceive('disableElement')->zeroOrMoreTimes();
    }
}
