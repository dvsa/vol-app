<?php

namespace CommonTest\Common\FormService\Form\Lva;

use Common\FormService\FormServiceManager;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\FormService\Form\Continuation\Declaration;
use Common\Form\Model\Form\Continuation\Declaration as FormModel;
use Common\Service\Helper\FormHelperService;

/**
 * Licence checklist form service test
 */
class DeclarationTest extends MockeryTestCase
{
    public $translator;
    public $scriptFactory;
    public $urlHelper;
    /** @var Declaration */
    protected $sut;

    /** @var  m\MockInterface */
    private $formHelper;

    #[\Override]
    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->translator = m::mock(TranslationHelperService::class);
        $this->scriptFactory = m::mock(ScriptFactory::class);
        $this->urlHelper = m::mock(UrlHelperService::class);
        $this->sut = new Declaration($this->formHelper, $this->translator, $this->scriptFactory, $this->urlHelper);
    }

    public function testGetFormSignatureDisabled(): void
    {
        $contentElement = m::mock();
        $declarationElement = m::mock();
        $versionElement = m::mock();
        $form = m::mock();

        $this->formHelper->shouldReceive('createForm')->with(FormModel::class)->once()->andReturn($form);
        $form->shouldReceive('get')->with('content')->andReturn($contentElement);
        $contentElement->shouldReceive('get')->with('declaration')->andReturn($declarationElement);

        $this->urlHelper->shouldReceive('fromRoute')->with('continuation/declaration/print', [], [], true)->once()
            ->andReturn('URL');
        $this->translator->shouldReceive('translate')->with('print-declaration-form')->once()
            ->andReturn('print-declaration-form');
        $this->translator->shouldReceive('translateReplace')
            ->with('undertakings_declaration_download', ['URL', 'print-declaration-form'])->once()
            ->andReturn('undertakings_declaration_download');
        $contentElement->shouldReceive('get')->with('declarationDownload')->andReturn(
            m::mock()->shouldReceive('setAttribute')->with('value', 'undertakings_declaration_download')->getMock()
        );
        $declarationElement->shouldReceive('setValue')->with('DECLARATIONS')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'content->signatureOptions')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'content->declarationForVerify')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'form-actions->sign')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'form-actions->submitAndPay')->once();
        $form->shouldReceive('get')->with('version')->andReturn($versionElement);
        $versionElement->shouldReceive('setValue')->with(34)->once();

        $this->formHelper->shouldReceive('remove')->with($form, 'signatureDetails')->once();

        $continuationDetailData = [
            'declarations' => 'DECLARATIONS',
            'disableSignatures' => true,
            'hasOutstandingContinuationFee' => false,
            'version' => 34,
        ];

        $this->assertEquals($form, $this->sut->getForm($continuationDetailData));
    }

    public function testGetFormSignatureEnabled(): void
    {
        $contentElement = m::mock();
        $declarationElement = m::mock();
        $versionElement = m::mock();
        $form = m::mock();

        $this->formHelper->shouldReceive('createForm')->with(FormModel::class)->once()->andReturn($form);
        $form->shouldReceive('get')->with('content')->andReturn($contentElement);
        $contentElement->shouldReceive('get')->with('declaration')->andReturn($declarationElement);

        $this->urlHelper->shouldReceive('fromRoute')->with('continuation/declaration/print', [], [], true)->once()
            ->andReturn('URL');
        $this->translator->shouldReceive('translate')->with('print-declaration-form')->once()
            ->andReturn('print-declaration-form');
        $this->translator->shouldReceive('translateReplace')
            ->with('undertakings_declaration_download', ['URL', 'print-declaration-form'])->once()
            ->andReturn('undertakings_declaration_download');
        $contentElement->shouldReceive('get')->with('declarationDownload')->andReturn(
            m::mock()->shouldReceive('setAttribute')->with('value', 'undertakings_declaration_download')->getMock()
        );
        $declarationElement->shouldReceive('setValue')->with('DECLARATIONS')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'content->disabledReview')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'form-actions->submitAndPay')->once();
        $form->shouldReceive('get')->with('version')->andReturn($versionElement);
        $versionElement->shouldReceive('setValue')->with(34)->once();

        $this->formHelper->shouldReceive('remove')->with($form, 'signatureDetails')->once();
        $this->scriptFactory->shouldReceive('loadFiles')->with(['continuation-declaration'])->once();

        $continuationDetailData = [
            'declarations' => 'DECLARATIONS',
            'disableSignatures' => false,
            'hasOutstandingContinuationFee' => false,
            'version' => 34,
        ];

        $this->assertEquals($form, $this->sut->getForm($continuationDetailData));
    }

    public function testGetFormNoFees(): void
    {
        $contentElement = m::mock();
        $declarationElement = m::mock();
        $versionElement = m::mock();
        $form = m::mock();

        $this->formHelper->shouldReceive('createForm')->with(FormModel::class)->once()->andReturn($form);
        $form->shouldReceive('get')->with('content')->andReturn($contentElement);
        $contentElement->shouldReceive('get')->with('declaration')->andReturn($declarationElement);

        $this->urlHelper->shouldReceive('fromRoute')->with('continuation/declaration/print', [], [], true)->once()
            ->andReturn('URL');
        $this->translator->shouldReceive('translate')->with('print-declaration-form')->once()
            ->andReturn('print-declaration-form');
        $this->translator->shouldReceive('translateReplace')
            ->with('undertakings_declaration_download', ['URL', 'print-declaration-form'])->once()
            ->andReturn('undertakings_declaration_download');
        $contentElement->shouldReceive('get')->with('declarationDownload')->andReturn(
            m::mock()->shouldReceive('setAttribute')->with('value', 'undertakings_declaration_download')->getMock()
        );
        $declarationElement->shouldReceive('setValue')->with('DECLARATIONS')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'content->disabledReview')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'form-actions->submitAndPay')->once();
        $form->shouldReceive('get')->with('version')->andReturn($versionElement);
        $versionElement->shouldReceive('setValue')->with(34)->once();

        $this->formHelper->shouldReceive('remove')->with($form, 'signatureDetails')->once();
        $this->scriptFactory->shouldReceive('loadFiles')->with(['continuation-declaration'])->once();

        $continuationDetailData = [
            'declarations' => 'DECLARATIONS',
            'disableSignatures' => false,
            'hasOutstandingContinuationFee' => false,
            'version' => 34,
        ];

        $this->assertEquals($form, $this->sut->getForm($continuationDetailData));
    }

    public function testGetFormWithFees(): void
    {
        $contentElement = m::mock();
        $declarationElement = m::mock();
        $versionElement = m::mock();
        $form = m::mock();

        $this->formHelper->shouldReceive('createForm')->with(FormModel::class)->once()->andReturn($form);
        $form->shouldReceive('get')->with('content')->andReturn($contentElement);
        $contentElement->shouldReceive('get')->with('declaration')->andReturn($declarationElement);

        $this->urlHelper->shouldReceive('fromRoute')->with('continuation/declaration/print', [], [], true)->once()
            ->andReturn('URL');
        $this->translator->shouldReceive('translate')->with('print-declaration-form')->once()
            ->andReturn('print-declaration-form');
        $this->translator->shouldReceive('translateReplace')
            ->with('undertakings_declaration_download', ['URL', 'print-declaration-form'])->once()
            ->andReturn('undertakings_declaration_download');
        $contentElement->shouldReceive('get')->with('declarationDownload')->andReturn(
            m::mock()->shouldReceive('setAttribute')->with('value', 'undertakings_declaration_download')->getMock()
        );
        $declarationElement->shouldReceive('setValue')->with('DECLARATIONS')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'content->disabledReview')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'form-actions->submit')->once();
        $form->shouldReceive('get')->with('version')->andReturn($versionElement);
        $versionElement->shouldReceive('setValue')->with(34)->once();

        $this->formHelper->shouldReceive('remove')->with($form, 'signatureDetails')->once();
        $this->scriptFactory->shouldReceive('loadFiles')->with(['continuation-declaration'])->once();

        $continuationDetailData = [
            'declarations' => 'DECLARATIONS',
            'disableSignatures' => false,
            'hasOutstandingContinuationFee' => true,
            'version' => 34,
        ];

        $this->assertEquals($form, $this->sut->getForm($continuationDetailData));
    }

    public function testGetFormReviewSection(): void
    {
        $contentElement = m::mock();
        $declarationElement = m::mock();
        $versionElement = m::mock();
        $form = m::mock();

        $this->formHelper->shouldReceive('createForm')->with(FormModel::class)->once()->andReturn($form);
        $form->shouldReceive('get')->with('content')->andReturn($contentElement);
        $contentElement->shouldReceive('get')->with('declaration')->andReturn($declarationElement);

        $this->urlHelper->shouldReceive('fromRoute')->with('continuation/declaration/print', [], [], true)->once()
            ->andReturn('URL');
        $this->translator->shouldReceive('translate')->with('print-declaration-form')->once()
            ->andReturn('print-declaration-form');
        $this->translator->shouldReceive('translateReplace')
            ->with('undertakings_declaration_download', ['URL', 'print-declaration-form'])->once()
            ->andReturn('undertakings_declaration_download');
        $contentElement->shouldReceive('get')->with('declarationDownload')->andReturn(
            m::mock()->shouldReceive('setAttribute')->with('value', 'undertakings_declaration_download')->getMock()
        );
        $declarationElement->shouldReceive('setValue')->with('DECLARATIONS')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'content->disabledReview')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'form-actions->submit')->once();
        $form->shouldReceive('get')->with('version')->andReturn($versionElement);
        $versionElement->shouldReceive('setValue')->with(34)->once();

        $contentElement->shouldReceive('get')->with('review')->andReturn(
            m::mock()->shouldReceive('setTokens')->with(['application.review-declarations.review.business-owner'])
                ->once()->getMock()
        );

        $this->formHelper->shouldReceive('remove')->with($form, 'signatureDetails')->once();
        $this->scriptFactory->shouldReceive('loadFiles')->with(['continuation-declaration'])->once();

        $continuationDetailData = [
            'declarations' => 'DECLARATIONS',
            'disableSignatures' => false,
            'hasOutstandingContinuationFee' => true,
            'version' => 34,
            'organisationTypeId' => RefData::ORG_TYPE_SOLE_TRADER,
        ];

        $this->assertEquals($form, $this->sut->getForm($continuationDetailData));
    }

    public function testGetFormSignature(): void
    {
        $contentElement = m::mock();
        $declarationElement = m::mock();
        $versionElement = m::mock();
        $form = m::mock();

        $this->formHelper->shouldReceive('createForm')->with(FormModel::class)->once()->andReturn($form);
        $form->shouldReceive('get')->with('content')->andReturn($contentElement);
        $contentElement->shouldReceive('get')->with('declaration')->andReturn($declarationElement);

        $this->urlHelper->shouldReceive('fromRoute')->with('continuation/declaration/print', [], [], true)->once()
            ->andReturn('URL');
        $this->translator->shouldReceive('translate')->with('print-declaration-form')->once()
            ->andReturn('print-declaration-form');
        $this->translator->shouldReceive('translateReplace')
            ->with('undertakings_declaration_download', ['URL', 'print-declaration-form'])->once()
            ->andReturn('undertakings_declaration_download');
        $contentElement->shouldReceive('get')->with('declarationDownload')->andReturn(
            m::mock()->shouldReceive('setAttribute')->with('value', 'undertakings_declaration_download')->getMock()
        );
        $declarationElement->shouldReceive('setValue')->with('DECLARATIONS')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'content->disabledReview')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'form-actions->submit')->once();
        $form->shouldReceive('get')->with('version')->andReturn($versionElement);
        $versionElement->shouldReceive('setValue')->with(34)->once();

        $contentElement->shouldReceive('get')->with('review')->andReturn(
            m::mock()->shouldReceive('setTokens')->with(['application.review-declarations.review.business-owner'])
                ->once()->getMock()
        );

        $this->translator->shouldReceive('translateReplace')->with('undertakings_signed', ['NAME', '14/07/2017'])
            ->once()->andReturn('SIGNATURE_DETAILS');
        $form->shouldReceive('get')->with('signatureDetails')->once()->andReturn(
            m::mock()->shouldReceive('get')->with('signature')->once()->andReturn(
                m::mock()->shouldReceive('setValue')->with('SIGNATURE_DETAILS')->once()->getMock()
            )->getMock()
        );

        $this->formHelper->shouldReceive('remove')->with($form, 'form-actions->sign')->once();
        $this->formHelper->shouldReceive('remove')->with($form, 'content')->once();

        $continuationDetailData = [
            'declarations' => 'DECLARATIONS',
            'disableSignatures' => false,
            'hasOutstandingContinuationFee' => true,
            'version' => 34,
            'organisationTypeId' => RefData::ORG_TYPE_SOLE_TRADER,
            'signature' => ['name' => 'NAME', 'date' => '2017-07-14'],
            'signatureType' => ['id' => RefData::SIGNATURE_TYPE_DIGITAL_SIGNATURE]
        ];

        $this->assertEquals($form, $this->sut->getForm($continuationDetailData));
    }
}
