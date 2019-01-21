<?php

namespace Permits\Data\Mapper;

use Common\Form\Form;
use Permits\Controller\Config\DataSource\AvailableCountries as AvailableCountriesDataSource;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;

/**
 *
 * Available Countries mapper
 */
class AvailableCountries
{
    /**
     * @param array $data
     * @param Form  $form
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form)
    {
        // set all available options
        $mapData = $data[AvailableCountriesDataSource::DATA_KEY];

        $valueOptions = [];

        foreach ($mapData['countries'] as $option) {
            $valueOptions[] = [
                'value' => $option['id'],
                'label' => $option['countryDesc'],
                'hint' => $option['countryDesc'],
            ];
        }

        $form->get('fields')->get('countries')->setValueOptions($valueOptions);

        // set already selected values
        $irhpApplication = $data[IrhpApplicationDataSource::DATA_KEY];

        $values = [];

        foreach ($irhpApplication['irhpPermitApplications'] as $irhpPermitApplication) {
            $countryId = $irhpPermitApplication['irhpPermitWindow']['irhpPermitStock']['country']['id'];

            if (!in_array($countryId, $values)) {
                $values[] = $countryId;
            }
        }

        $form->get('fields')->get('countries')->setValue($values);

        return $data;
    }
}
