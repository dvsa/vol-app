<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\Form\Form;
use Permits\Controller\Config\DataSource\AvailableCountries as AvailableCountriesDataSource;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpApplicationDataSource;

/**
 *
 * Available Countries mapper
 */
class AvailableCountries implements MapperInterface
{
    use MapFromResultTrait;

    /**
     * @param Form  $form
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
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

        foreach ($irhpApplication['countrys'] as $country) {
            $countryId = $country['id'];

            if (!in_array($countryId, $values)) {
                $values[] = $countryId;
            }
        }

        $form->get('fields')->get('countries')->setValue($values);

        return $data;
    }
}
