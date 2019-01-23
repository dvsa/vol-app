<?php

namespace Permits\Data\Mapper;

use Common\Form\Form;
use Permits\Controller\Config\DataSource\AvailableTypes as AvailableTypesDataSource;

/**
 *
 * Available types mapper
 */
class AvailableTypes
{
    /**
     * @param array $data
     * @param Form  $form
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form)
    {
        $mapData = $data[AvailableTypesDataSource::DATA_KEY];

        $valueOptions = [];

        foreach ($mapData['types'] as $option) {
            $valueOptions[] = [
                'value' => $option['id'],
                'label' => $option['name']['description'],
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $option['description'],
            ];
        }

        $form->get('fields')->get('type')->setValueOptions($valueOptions);

        $data['guidance'] = [
            'disableHtmlEscape' => true,
            'value' => 'permits.page.type.hint',
        ];

        return $data;
    }
}
