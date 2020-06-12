<?php

namespace Permits\Data\Mapper;

use Common\Form\Form;
use Permits\Controller\Config\DataSource\AvailableCountries;

/**
 * Removed countries mapper
 */
class RemovedCountries
{
    /**
     * @param array $data
     * @param Form $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
    {
        $form->get('fields')->get('countries')->setValue(
            $data['validatedSelectedCountryCodesCsv']
        );

        $this->populateRemovedCountries($data, $form);

        return $data;
    }

    /**
     * Add markup to the form containing a bullet list of removed countries
     *
     * @param array $data
     * @param Form $form
     */
    public function populateRemovedCountries(array $data, $form)
    {
        $availableCountriesMap = [];
        foreach ($data[AvailableCountries::DATA_KEY]['countries'] as $country) {
            $countryCode = $country['id'];
            $countryName = $country['countryDesc'];
            $availableCountriesMap[$countryCode] = $countryName;
        }

        $removedCountryNames = [];
        foreach ($data['removedCountryCodes'] as $countryCode) {
            $removedCountryNames[] = $availableCountriesMap[$countryCode];
        }
        sort($removedCountryNames);

        $markup = '<ul class="govuk-list govuk-list--bullet">';
        foreach ($removedCountryNames as $countryName) {
            $markup .= '<li>' . $countryName . '</li>';
        }
        $markup .= '</ul>';

        $form->get('fields')->get('removedCountries')->setValue($markup);
    }
}
