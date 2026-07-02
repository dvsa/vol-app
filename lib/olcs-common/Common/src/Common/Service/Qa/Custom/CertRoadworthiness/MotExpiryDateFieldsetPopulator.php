<?php

namespace Common\Service\Qa\Custom\CertRoadworthiness;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\Custom\Common\DateSelectMustBeBefore;
use Common\Service\Qa\Custom\Common\HtmlAdder;
use Common\Service\Qa\Custom\Common\FileUploadFieldsetGenerator;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Laminas\Form\Fieldset;

class MotExpiryDateFieldsetPopulator implements FieldsetPopulatorInterface
{
    public const UPLOAD_HINT_PRIORITY = 100;

    /**
     * Create service instance
     *
     * @param FileUploadFieldsetGenerator
     *
     * @return MotExpiryDateFieldsetPopulator
     */
    public function __construct(private TranslationHelperService $translator, private HtmlAdder $htmlAdder, private FileUploadFieldsetGenerator $fileUploadFieldsetGenerator)
    {
    }

    /**
     * Populate the fieldset with elements based on the supplied options array
     *
     * @param mixed $form
     */
    #[\Override]
    public function populate($form, Fieldset $fieldset, array $options): void
    {
        $markup = sprintf(
            '<legend class="govuk-heading-m">%s</legend><div class="govuk-hint">%s</div>',
            $this->translator->translate('qanda.certificate-of-roadworthiness.mot-expiry-date.legend'),
            $this->translator->translate('qanda.certificate-of-roadworthiness.mot-expiry-date.hint')
        );

        $this->htmlAdder->add($fieldset, 'hint', $markup);

        $dateWithThresholdOptions = $options['dateWithThreshold'];

        $fieldset->add(
            [
                'name' => 'qaElement',
                'type' => DateSelectMustBeBefore::class,
                'options' => [
                    'dateMustBeBefore' => $dateWithThresholdOptions['dateThreshold'],
                    'invalidDateKey' => 'qanda.certificate-of-roadworthiness.mot-expiry-date.error.date-invalid',
                    'dateInPastKey' => 'qanda.certificate-of-roadworthiness.mot-expiry-date.error.date-in-past',
                    'dateNotBeforeKey' => 'qanda.certificate-of-roadworthiness.mot-expiry-date.error.date-too-far',
                    'create_empty_option' => true,
                ],
                'attributes' => [
                    'value' => $dateWithThresholdOptions['date']['value']
                ]
            ]
        );

        if ($options['enableFileUploads']) {
            $uploadFieldset = $this->fileUploadFieldsetGenerator->generate();

            $markup = sprintf(
                '<legend class="govuk-heading-m">%s</legend><div class="govuk-hint">%s</div>',
                $this->translator->translate('qanda.certificate-of-roadworthiness.mot-expiry-date.upload.legend'),
                $this->translator->translate('qanda.certificate-of-roadworthiness.mot-expiry-date.upload.hint')
            );

            $this->htmlAdder->add($uploadFieldset, 'uploadHint', $markup, self::UPLOAD_HINT_PRIORITY);

            $form->add($uploadFieldset);
        }
    }
}
