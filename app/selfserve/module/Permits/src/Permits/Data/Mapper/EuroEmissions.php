<?php

namespace Permits\Data\Mapper;

use Common\Exception\BadRequestException;
use Common\Service\Helper\TranslationHelperService;
use Common\RefData;

/**
 *
 * Euro Emissions mapper
 */
class EuroEmissions
{
    /**
     * @param array                    $data
     * @param                          $form
     * @param TranslationHelperService $translator
     *
     * @return array
     * @throws BadRequestException
     */
    public static function mapForFormOptions(array $data, $form, TranslationHelperService $translator)
    {
        $emissionsCategory = $data['windows']['windows'][0]['emissionsCategory']['id'];

        switch ($emissionsCategory) {
            case RefData::EMISSIONS_CATEGORY_EURO6:
                $data['question'] = 'permits.page.euro6.emissions.question';
                $data['additionalGuidance'] = [
                    'permits.page.euro6.emissions.guidance.line.1',
                    'permits.page.euro6.emissions.guidance.line.2'
                ];
                $label = 'permits.form.euro6.label';
                break;
            case RefData::EMISSIONS_CATEGORY_EURO5:
                $data['question'] = 'permits.page.euro5.emissions.question';
                $data['additionalGuidance'] = [
                    'permits.page.euro5.emissions.guidance.line.1',
                    'permits.page.euro5.emissions.guidance.line.2'
                ];
                $label = 'permits.form.euro5.label';
                break;
        }

        $emissionsField = $form->get('fields')->get('emissions');

        $emissionsField->setValue($data['application']['emissions']);
        $emissionsField->setLabel($label);

        return $data;
    }
}
