<?php

namespace Permits\Data\Mapper;

use Common\RefData;
use Permits\Controller\Config\DataSource\EcmtConstrainedCountriesList as EcmtConstrainedCountriesDataSource;
use Permits\Controller\Config\DataSource\PermitApplication as PermitAppDataSource;
use Zend\Form\Form;

/**
 * Restricted Countries mapper
 */
class RestrictedCountries
{
    /**
     * @param array $data
     * @param Form $form
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, Form $form)
    {

        if ($data[PermitAppDataSource::DATA_KEY]['windowEmissionsCategory'] === RefData::EMISSIONS_CATEGORY_EURO5) {
            $form->remove('fields');
            $data['guidance'] = 'permits.page.restricted-countries.guidance.euro5';
            $data['question'] = 'permits.page.restricted-countries.title.euro5';
        } else {
            $form->remove('euro5Fields');
            $data['guidance'] = [
                'permits.page.restricted-countries.guidance.line.1',
                'permits.page.restricted-countries.guidance.line.2'
            ];
            $data['question'] = 'permits.page.restricted-countries.question';

            $mapData = $data[EcmtConstrainedCountriesDataSource::DATA_KEY];
            $valueOptions = [];
            $applicationCountries = array_column($data[PermitAppDataSource::DATA_KEY]['countrys'], 'id');
            foreach ($mapData['results'] as $option) {
                $selected = false;
                if (in_array($option['id'], $applicationCountries)) {
                    $selected = true;
                };
                $valueOptions[] = [
                    'value' => $option['id'],
                    'label' => $option['countryDesc'],
                    'selected' => $selected
                ];
            }
            $form->get('fields')->get('yesContent')->get('restrictedCountriesList')->setValueOptions($valueOptions);
        }

        return $data;
    }

    /**
     * Map data from the 2 potential Restricted Country forms (one euro5 one euro6) ready for DTO
     *
     * @param array $data Data from form
     * @return array
     */
    public static function mapFromForm(array $data): array
    {
        $data['fields']['countryIds'] = isset($data['fields']['yesContent']['restrictedCountriesList']) ? $data['fields']['yesContent']['restrictedCountriesList'] : [];
        return $data['fields'];
    }

    /**
     * Pre-process post data before its passed to $form->setData
     *
     * @param array $data
     * @return array
     */
    public static function preprocessFormData(array $data): array
    {
        if (isset($data['euro5Fields'])) {
            $data['fields']['restrictedCountries'] = $data['euro5Fields']['restrictedCountries'];
            $data['fields']['yesContent']['restrictedCountriesList'] = [];
        }
        return $data;
    }
}
