<?php

namespace Permits\Data\Mapper;

use Common\Form\Form;
use Permits\Controller\Config\DataSource\AvailableYears as AvailableYearsDataSource;

/**
 * Available years mapper
 */
class AvailableYears
{
    /**
     * @param array $data
     * @param Form  $form
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form)
    {
        $years = $data[AvailableYearsDataSource::DATA_KEY]['years'];
        $valueOptions = [];

        foreach ($years as $year) {
            $valueOptions[] = [
                'value' => $year,
                'label' => $year,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
            ];
        }

        $form->get('fields')->get('year')->setValueOptions($valueOptions);

        if (count($years) > 1) {
            $data['hint'] = 'permits.page.year.hint.multiple-years-available';
        }

        return $data;
    }
}
