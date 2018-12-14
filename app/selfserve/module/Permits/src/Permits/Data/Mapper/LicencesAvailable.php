<?php

namespace Permits\Data\Mapper;

use Permits\Controller\Config\DataSource\LicencesAvailable as LicencesAvailableDataSource;

/**
 *
 * Available Licences mapper
 */
class LicencesAvailable
{
    /**
     * @param array $data
     * @param       $form
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form)
    {
        $mapData = $data[LicencesAvailableDataSource::DATA_KEY];
        $isNew = !isset($data['application']);
        $valueOptions = [];

        foreach ($mapData['eligibleEcmtLicences']['result'] as $key => $option) {
            $selected = false;

            if (!$option['canMakeEcmtApplication']) {
                if ($isNew || $option['id'] !== $data['application']['licence']['id']) {
                    continue;
                } else {
                    $selected = true;
                }
            }

            if ($option['licenceType']['id'] === \Common\RefData::LICENCE_TYPE_RESTRICTED) {
                $data['guidance'] = 'permits.form.ecmt-licence.restricted-licence.hint';
            }

            $valueOptions[] = [
                'value' => $option['id'],
                'label' => $option['licNo'],
                'label_attributes' => 'govuk-label govuk-radios__label govuk-label--s',
                'hint' => $option['licenceType']['description'] . ' (' . $option['trafficArea'] . ')',
                'selected' => $selected
            ];
        }

        if (count($valueOptions) === 1) {
            $valueOptions[0]['label_attributes'] = ['class' => 'visually-hidden'];
            $valueOptions[0]['selected'] = true;
            $data['question'] = 'permits.page.licence.question.one.licence';
            $data['questionArgs'] = [str_replace('@', ' ', $valueOptions[0]['label'])];
        }

        $form->get('fields')->get('licence')->setValueOptions($valueOptions);

        return $data;
    }
}
