<?php

namespace Permits\Data\Mapper;

use Common\Form\Elements\Types\HtmlTranslated;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
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
     * @param TranslationHelperService $translator
     * @return array
     */
    public static function mapForFormOptions(array $data, $form)
    {
        $mapData = $data[LicencesAvailableDataSource::DATA_KEY];
        $isNew = !isset($data['application']['licence']);
        $isEcmt = false;
        $isBilateral = false;

        if (self::inArrayR(RefData::PERMIT_TYPE_ECMT, $data)) {
            $isEcmt = true;
        } elseif (self::inArrayR(RefData::PERMIT_TYPE_ANNUAL_BILATERAL, $data)) {
            $isBilateral = true;
        }

        $valueOptions = [];

        foreach ($mapData['eligibleLicences']['result'] as $key => $option) {
            $selected = !$isNew ? $option['id'] === $data['application']['licence']['id'] && empty($data['active']) : false;

            if ($isEcmt) {
                if (!$option['canMakeEcmtApplication']) {
                    if ($isNew || $option['id'] !== $data['application']['licence']['id']) {
                        continue;
                    }
                }

                if ($option['licenceType']['id'] === \Common\RefData::LICENCE_TYPE_RESTRICTED) {
                    $valueOptions[$option['id']][$option['id'].'Content'] = 'en_GB/markup-ecmt-restricted-licence-conditional';
                    $content = new HtmlTranslated($option['id'] . 'Content');
                    $content->setValue('permits.form.ecmt-licence.restricted-licence.hint');
                    $form->get('fields')->add($content);
                }
            } else {
                if ($isBilateral && isset($data['active']) && $option['id'] == $data['active']) {
                    $data['warning'] = 'permits.irhp.bilateral.already-applied';
                    $selected = true;
                }
            }

            $valueOptions[$option['id']] = [
                'value' => $option['id'],
                'label' => $option['licNo'],
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $option['licenceType']['description'] . ' (' . $option['trafficArea'] . ')',
                'selected' => $selected,
            ];
        }

        if ($isEcmt) {
            if (count($valueOptions) === 1) {
                $key = array_keys($valueOptions)[0];
                $data['question'] = 'permits.page.licence.question.one.licence';
                $data['questionArgs'] = [$valueOptions[$key]['label'] . ' ' . $valueOptions[$key]['hint']];
                $valueOptions[$key]['selected'] = true;
                $form->get('fields')->get('licence')->setAttribute('radios_wrapper_attributes', ['class' => 'visually-hidden']);
            }
        }

        $form->get('fields')->get('licence')->setValueOptions($valueOptions);

        return $data;
    }

    /**
     * A recursive function for searching nested arrays for a value.
     *
     * @param $needle
     * @param $haystack
     * @return bool
     */
    private static function inArrayR($needle, $haystack)
    {
        $found = false;

        foreach ($haystack as $item) {
            if ($item === $needle) {
                $found = true;
                break;
            } elseif (is_array($item)) {
                $found = self::inArrayR($needle, $item);
                if ($found) {
                    break;
                }
            }
        }

        return $found;
    }
}
