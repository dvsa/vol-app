<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\Form\Form;
use Permits\Controller\Config\DataSource\AvailableTypes as AvailableTypesDataSource;

/**
 * Available types mapper
 */
class AvailableTypes implements MapperInterface
{
    use MapFromResultTrait;

    /**
     * @param array $data
     * @param Form  $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
    {
        $mapData = $data[AvailableTypesDataSource::DATA_KEY];

        $valueOptions = [];
        $selectedId = $mapData['selectedType'];

        foreach ($mapData['types'] as $option) {
            $optionId = $option['id'];

            $valueOptions[] = [
                'value' => $optionId,
                'label' => $option['name']['description'],
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $option['description'],
                'selected' => $optionId == $selectedId
            ];
        }

        if (count($valueOptions)) {
            $valueOptions[0]['attributes'] = [
                'id' => 'type'
            ];
        }

        $form->get('fields')->get('type')->setValueOptions($valueOptions);

        return $data;
    }
}
