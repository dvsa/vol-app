<?php

namespace Permits\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
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

        $isEcmtShortTerm = ($data['type'] == RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID);

        foreach ($years as $year) {
            $label = $year;

            if ($isEcmtShortTerm && ($label == 2019)) {
                $label = 'permits.page.year.ecmt-short-term.label.2019';
            }

            $valueOptions[] = [
                'value' => $year,
                'label' => $label,
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
