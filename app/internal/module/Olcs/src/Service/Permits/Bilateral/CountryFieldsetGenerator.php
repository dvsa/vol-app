<?php

namespace Olcs\Service\Permits\Bilateral;

use Common\Service\Helper\TranslationHelperService;
use Laminas\Form\Element\Select;
use Laminas\Form\Factory as FormFactory;
use Laminas\Form\Fieldset;

/**
 * Country fieldset generator
 */
class CountryFieldsetGenerator
{
    /** @var TranslationHelperService */
    protected $translator;

    /** @var FormFactory */
    protected $formFactory;

    /** @var PeriodFieldsetGenerator */
    protected $periodFieldsetGenerator;

    /**
     * Create service instance
     *
     *
     * @return CountryFieldsetGenerator
     */
    public function __construct(
        TranslationHelperService $translator,
        FormFactory $formFactory,
        PeriodFieldsetGenerator $periodFieldsetGenerator
    ) {
        $this->translator = $translator;
        $this->formFactory = $formFactory;
        $this->periodFieldsetGenerator = $periodFieldsetGenerator;
    }

    /**
     * Return a Fieldset element corresponding to the provided data
     *
     *
     * @return Fieldset
     */
    public function generate(array $country)
    {
        $countryFieldset = $this->formFactory->create(
            [
                'type' => Fieldset::class,
                'name' => $country['id'],
                'options' => [
                    'label' => $country['name']
                ],
                'attributes' => [
                    'data-role' => 'country',
                    'data-type' => $country['type'],
                    'data-id' => $country['id'],
                    'data-name' => $country['name']
                ]
            ]
        );

        $periods = $country['periods'];

        $countryFieldset->add(
            $this->generatePeriodSelector($periods, $country['periodLabel'], $country['selectedPeriodId'])
        );

        $periodsFieldset = $this->formFactory->create(
            [
                'type' => Fieldset::class,
                'name' => 'periods'
            ]
        );

        foreach ($periods as $period) {
            $periodsFieldset->add(
                $this->periodFieldsetGenerator->generate($period, $country['type'])
            );
        }

        $countryFieldset->add($periodsFieldset);

        return $countryFieldset;
    }

    /**
     * Return a Laminas Select element corresponding to the provided list of periods
     *.
     * @param string $periodLabel
     * @param int|null $selectedPeriodId
     * @return Select
     */
    private function generatePeriodSelector(array $periods, $periodLabel, $selectedPeriodId)
    {
        $valueOptions = ['' => $periodLabel];
        foreach ($periods as $period) {
            $valueOptions[$period['id']] = $this->translator->translate($period['key']);
        }

        return $this->formFactory->create(
            [
                'type' => Select::class,
                'name' => 'selectedPeriodId',
                'options' => [
                    'label' => $periodLabel,
                    'value_options' => $valueOptions
                ],
                'attributes' => [
                    'value' => $selectedPeriodId
                ]
            ]
        );
    }
}
