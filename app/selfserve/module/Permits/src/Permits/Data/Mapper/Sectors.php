<?php

namespace Permits\Data\Mapper;

use Permits\Controller\Config\DataSource\Sectors as SectorsDataSource;

/**
 *
 * Sectors mapper
 */
class Sectors
{
    /**
     * @param array $data
     * @param       $form
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form)
    {
        $mapData = $data[SectorsDataSource::DATA_KEY];
        $valueOptions = [];

        foreach ($mapData['results'] as $option) {
            $selected = false;

            if (isset($data['application']['sectors']['id']) && $option['id'] === $data['application']['sectors']['id']) {
                $selected = true;
            }

            $valueOptions[] = [
                'value' => $option['id'],
                'label' => $option['name'],
                'hint' => $option['description'],
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'selected' => $selected
            ];
        }

        $form->get('fields')->get('sector')->setValueOptions($valueOptions);

        return $data;
    }
}
