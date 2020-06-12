<?php

namespace Olcs\Service\Permits\Bilateral;

use Zend\Form\Element\Hidden;
use Zend\Form\Factory as FormFactory;
use Zend\Form\Fieldset;
use Zend\Form\Form;

/**
 * Application form populator
 */
class ApplicationFormPopulator
{
    /** @var FormFactory */
    protected $formFactory;

    /** @var CountryFieldsetGenerator */
    protected $countryFieldsetGenerator;

    /**
     * Create service instance
     *
     * @param FormFactory $formFactory
     * @param CountryFieldsetGenerator $countryFieldsetGenerator
     *
     * @return ApplicationFormPopulator
     */
    public function __construct(FormFactory $formFactory, CountryFieldsetGenerator $countryFieldsetGenerator)
    {
        $this->formFactory = $formFactory;
        $this->countryFieldsetGenerator = $countryFieldsetGenerator;
    }

    /**
     * Add form elements corresponding to the provided bilateral metadata
     *
     * @param Form $form
     * @param array $data
     *
     * @return array
     */
    public function populate(Form $form, array $data)
    {
        $fieldset = $this->formFactory->create(
            [
                'type' => Fieldset::class,
                'name' => 'fields',
                'attributes' => [
                    'id' => 'bilateralContainer'
                ]
            ]
        );

        $fieldset->add(
            [
                'type' => Hidden::class,
                'name' => 'selectedCountriesCsv',
                'attributes' => [
                    'id' => 'selectedCountriesCsv',
                    'value' => implode(',', $data['selectedCountryIds'])
                ]
            ]
        );

        $countriesFieldset = $this->formFactory->create(
            [
                'type' => Fieldset::class,
                'name' => 'countries'
            ]
        );

        foreach ($data['bilateralMetadata']['countries'] as $country) {
            $countriesFieldset->add(
                $this->countryFieldsetGenerator->generate($country)
            );
        }

        $fieldset->add($countriesFieldset);
        $form->add($fieldset);
    }
}
