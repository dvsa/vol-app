<?php

namespace Permits\Data\Mapper;

use Common\Form\Elements\Types\HtmlTranslated;
use Permits\Controller\Config\DataSource\LicencesAvailable as LicencesAvailableDataSource;

/**
 * Available Licences mapper
 */
class LicencesAvailable
{
    const ECMT_RESTRICTED_HINT = 'permits.form.ecmt-licence.restricted-licence.hint';

    /**
     * @param array $data
     * @param       $form
     *
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
    {
        $mapData = $data[LicencesAvailableDataSource::DATA_KEY];
        $selectedId = $mapData['selectedLicence'];

        $valueOptions = [];
        $fieldSet = $form->get('fields');

        foreach ($mapData['eligibleLicences'] as $option) {
            $id = $option['id'];
            $idContent = $id . 'Content';
            $activeId = $data['active'] ?? null;

            if ($mapData['isEcmtAnnual'] && $option['isRestricted']) {
                $valueOptions[$id][$idContent] = 'en_GB/markup-ecmt-restricted-licence-conditional';
                $content = new HtmlTranslated($idContent);
                $content->setValue(self::ECMT_RESTRICTED_HINT);
                $fieldSet->add($content);
            }

            if ($id == $activeId) {
                $data['warning'] = 'permits.irhp.bilateral.already-applied';
                $selectedId = $id;
            }

            $valueOptions[$id] = [
                'value' => $id,
                'label' => $option['licNo'],
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $option['licenceTypeDesc'] . ' (' . $option['trafficArea'] . ')',
                'selected' => $id === $selectedId,
            ];
        }

        if (count($valueOptions)) {
            $key = array_keys($valueOptions)[0];
            $valueOptions[$key]['attributes'] = [
                'id' => 'licence'
            ];
        }

        $fieldSet->get('licence')->setValueOptions($valueOptions);

        return $data;
    }
}
