<?php

namespace Permits\Data\Mapper;

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
        $mapData = $data[EcmtConstrainedCountriesDataSource::DATA_KEY];
        $valueOptions = [];
        $applicationCountries = array_column($data[PermitAppDataSource::DATA_KEY]['countrys'], 'id');

        foreach ($mapData['results'] as $option) {
            $selected = in_array($option['id'], $applicationCountries);

            $valueOptions[] = [
                'value' => $option['id'],
                'label' => $option['countryDesc'],
                'selected' => $selected
            ];
        }

        $form->get('fields')->get('yesContent')->get('restrictedCountriesList')->setValueOptions($valueOptions);

        if (!is_null($data[PermitAppDataSource::DATA_KEY]['hasRestrictedCountries'])) {
            $restrictedCountries = $data[PermitAppDataSource::DATA_KEY]['hasRestrictedCountries'] == true ? 1 : 0;

            $form->get('fields')
                ->get('restrictedCountries')
                ->setValue($restrictedCountries);
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
        $data['fields']['countryIds'] =
            (int)$data['fields']['restrictedCountries'] === 1 && isset($data['fields']['yesContent']['restrictedCountriesList'])
                ? $data['fields']['yesContent']['restrictedCountriesList'] : [];

        return $data['fields'];
    }

    /**
     * Pre-process post data before its passed to $form->setData
     *
     * @param array $data
     * @param Form $form
     * @return array
     */
    public static function preprocessFormData(array $data, Form $form): array
    {
        $preProcess = [];
        if (isset($data['fields'])) {
            if ((int)$data['fields']['restrictedCountries'] === 1 && empty($data['fields']['yesContent']['restrictedCountriesList'])) {
                $form->get('fields')
                    ->get('yesContent')
                    ->get('restrictedCountriesList')
                    ->setMessages(['error.messages.restricted.countries.list']);
                $preProcess['invalidForm'] = true;
            }
        }

        $preProcess['formData'] = $data;
        return $preProcess;
    }
}
