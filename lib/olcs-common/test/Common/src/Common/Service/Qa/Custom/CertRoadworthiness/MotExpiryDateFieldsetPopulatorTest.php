<?php

declare(strict_types=1);

namespace CommonTest\Service\Qa\Custom\CertRoadworthiness;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\CertRoadworthiness\MotExpiryDateFieldsetPopulator;
use Common\Service\Qa\Custom\Common\DateSelectMustBeBefore;
use Common\Service\Qa\Custom\Common\FileUploadFieldsetGenerator;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Form\Fieldset;
use Laminas\Form\Form;

class MotExpiryDateFieldsetPopulatorTest extends MockeryTestCase
{
    public const REQUESTED_DATE = '2020-03-15';

    public const DATE_MUST_BE_BEFORE = '2020-05-01';

    private $translator;

    private $form;

    private $fieldset;

    private $htmlAdder;

    private $fileUploadFieldsetGenerator;

    private $motExpiryDateFieldsetPopulator;

    #[\Override]
    protected function setUp(): void
    {
        $this->translator = m::mock(TranslationHelperService::class);
        $this->translator->shouldReceive('translate')
            ->with('qanda.certificate-of-roadworthiness.mot-expiry-date.legend')
            ->andReturn('MOT expiry date');
        $this->translator->shouldReceive('translate')
            ->with('qanda.certificate-of-roadworthiness.mot-expiry-date.hint')
            ->andReturn('For example, 10 12 2019.');

        $this->form = m::mock(Form::class);

        $this->fieldset = m::mock(Fieldset::class);

        $expectedMarkup = '<legend class="govuk-heading-m">MOT expiry date</legend>' .
            '<div class="govuk-hint">For example, 10 12 2019.</div>';

        $this->htmlAdder = m::mock(HtmlAdder::class);
        $this->htmlAdder->shouldReceive('add')
            ->with($this->fieldset, 'hint', $expectedMarkup)
            ->once()
            ->globally()
            ->ordered();

        $this->fileUploadFieldsetGenerator = m::mock(FileUploadFieldsetGenerator::class);

        $expectedElementSpecification = [
            'name' => 'qaElement',
            'type' => DateSelectMustBeBefore::class,
            'options' => [
                'dateMustBeBefore' => self::DATE_MUST_BE_BEFORE,
                'invalidDateKey' => 'qanda.certificate-of-roadworthiness.mot-expiry-date.error.date-invalid',
                'dateInPastKey' => 'qanda.certificate-of-roadworthiness.mot-expiry-date.error.date-in-past',
                'dateNotBeforeKey' => 'qanda.certificate-of-roadworthiness.mot-expiry-date.error.date-too-far',
                'create_empty_option' => true,
            ],
            'attributes' => [
                'value' => self::REQUESTED_DATE
            ]
        ];

        $this->fieldset->shouldReceive('add')
            ->with($expectedElementSpecification)
            ->once()
            ->globally()
            ->ordered();

        $this->motExpiryDateFieldsetPopulator = new MotExpiryDateFieldsetPopulator(
            $this->translator,
            $this->htmlAdder,
            $this->fileUploadFieldsetGenerator
        );
    }

    public function testPopulateEnableFileUploadsFalse(): void
    {
        $options = [
            'enableFileUploads' => false,
            'dateWithThreshold' => [
                'dateThreshold' => self::DATE_MUST_BE_BEFORE,
                'date' => [
                    'value' => self::REQUESTED_DATE
                ]
            ]
        ];

        $this->motExpiryDateFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }

    public function testPopulateEnableFileUploadsTrue(): void
    {
        $this->translator->shouldReceive('translate')
            ->with('qanda.certificate-of-roadworthiness.mot-expiry-date.upload.legend')
            ->andReturn('Upload a copy of your MOT certificate');
        $this->translator->shouldReceive('translate')
            ->with('qanda.certificate-of-roadworthiness.mot-expiry-date.upload.hint')
            ->andReturn('You need to upload a scanned copy or clear photo of your latest MOT certificate.');

        $fileUploadFieldset = m::mock(Fieldset::class);

        $this->fileUploadFieldsetGenerator->shouldReceive('generate')
            ->withNoArgs()
            ->andReturn($fileUploadFieldset);

        $expectedMarkup = '<legend class="govuk-heading-m">Upload a copy of your MOT certificate</legend>' .
            '<div class="govuk-hint">' .
            'You need to upload a scanned copy or clear photo of your latest MOT certificate.' .
            '</div>';

        $this->htmlAdder->shouldReceive('add')
            ->with($fileUploadFieldset, 'uploadHint', $expectedMarkup, 100)
            ->once()
            ->globally()
            ->ordered();

        $this->form->shouldReceive('add')
            ->with($fileUploadFieldset)
            ->once()
            ->globally()
            ->ordered();

        $options = [
            'enableFileUploads' => true,
            'dateWithThreshold' => [
                'dateThreshold' => self::DATE_MUST_BE_BEFORE,
                'date' => [
                    'value' => self::REQUESTED_DATE
                ]
            ]
        ];

        $this->motExpiryDateFieldsetPopulator->populate($this->form, $this->fieldset, $options);
    }
}
