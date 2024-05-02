<?php

namespace Olcs\Service\Permits\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Olcs\Form\Element\Permits\BilateralNoOfPermitsElement;
use Laminas\Form\Factory as FormFactory;
use Laminas\Form\Fieldset;

/**
 * No of permits element generator
 */
class NoOfPermitsElementGenerator
{
    /** @var TranslationHelperService */
    protected $translator;

    /** @var FormFactory */
    protected $formFactory;

    /**
     * Create service instance
     *
     *
     * @return NoOfPermitsElementGenerator
     */
    public function __construct(TranslationHelperService $translator, FormFactory $formFactory)
    {
        $this->translator = $translator;
        $this->formFactory = $formFactory;
    }

    /**
     * Return a Laminas text element corresponding to the provided field data
     *
     *
     * @return BilateralNoOfPermitsElement
     */
    public function generate(array $field)
    {
        $labelTranslationKey = sprintf(
            'permits.irhp.range.type.%s.%s',
            $field['cabotage'],
            str_replace('journey_', '', $field['journey'])
        );

        return $this->formFactory->create(
            [
                'type' => BilateralNoOfPermitsElement::class,
                'name' => $field['cabotage'] . '-' . $field['journey'],
                'options' => [
                    'label' => $this->translator->translate($labelTranslationKey)
                ],
                'attributes' => [
                    'value' => $field['value']
                ]
            ]
        );
    }
}
