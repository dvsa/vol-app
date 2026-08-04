<?php

namespace Common\Service\Qa\Custom\Ecmt;

use Common\Service\Helper\TranslationHelperService;
use Common\Service\Qa\FieldsetPopulatorInterface;
use Common\Service\Qa\RadioFieldsetPopulator;
use Laminas\Form\Fieldset;

class SectorsFieldsetPopulator implements FieldsetPopulatorInterface
{
    /**
     * Create service instance
     *
     *
     * @return SectorsFieldsetPopulator
     */
    public function __construct(private TranslationHelperService $translator, private RadioFieldsetPopulator $radioFieldsetPopulator)
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
        $this->radioFieldsetPopulator->populate($form, $fieldset, $options);

        $qaElement = $fieldset->get('qaElement');
        $valueOptions = $qaElement->getValueOptions();

        $markupBefore = sprintf(
            '<div class="govuk-radios__divider">%s</div>',
            $this->translator->translate('qanda.ecmt.sectors.divider.or')
        );

        // we assume that the markup needs to be added before the final radio button
        $valueOptions[count($valueOptions) - 1]['markup_before'] = $markupBefore;

        $qaElement->setValueOptions($valueOptions);
    }
}
