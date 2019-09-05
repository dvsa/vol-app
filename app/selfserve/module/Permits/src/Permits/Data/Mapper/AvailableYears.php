<?php

namespace Permits\Data\Mapper;

use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Permits\Controller\Config\DataSource\AvailableYears as AvailableYearsDataSource;
use RuntimeException;

/**
 * Available years mapper
 */
class AvailableYears
{
    /**
     * @param array $data
     * @param Form  $form
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form, TranslationHelperService $translator)
    {
        switch ($data['type']) {
            case RefData::ECMT_PERMIT_TYPE_ID:
                return self::mapForEcmtAnnual($data, $form);
            case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                return self::mapForEcmtShortTerm($data, $form, $translator);
            default:
                throw new RuntimeException('This mapper does not support permit type ' . $data['type']);
        }
    }

    /**
     * Map year options for ECMT annual permit type
     *
     * @param array $data
     * @param Form  $form
     *
     * @return array
     */
    private static function mapForEcmtAnnual(array $data, $form)
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

        $data['browserTitle'] = 'permits.page.year.browser.title';
        $data['question'] = 'permits.page.year.question';

        $data['hint'] = 'permits.page.year.hint.one-year-available';
        if (count($years) > 1) {
            $data['hint'] = 'permits.page.year.hint.multiple-years-available';
        }

        return $data;
    }

    /**
     * Map year options for ECMT short term permit type
     *
     * @param array $data
     * @param Form  $form
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    private static function mapForEcmtShortTerm(array $data, $form, TranslationHelperService $translator)
    {
        $years = $data[AvailableYearsDataSource::DATA_KEY]['years'];
        $valueOptions = [];

        foreach ($years as $year) {
            $hint = 'permits.page.year.ecmt-short-term.option.hint.not-2019';
            if ($year == 2019) {
                $hint = 'permits.page.year.ecmt-short-term.option.hint.2019';
            }

            $valueOptions[] = [
                'value' => $year,
                'label' => $year,
                'label_attributes' => ['class' => 'govuk-label govuk-radios__label govuk-label--s'],
                'hint' => $translator->translateReplace($hint, [$year]),
            ];
        }

        $form->get('fields')->get('year')->setValueOptions($valueOptions);

        $suffix = 'one-year-available';
        if (count($years) > 1) {
            $suffix = 'multiple-years-available';
        }

        $data['hint'] = 'permits.page.year.hint.' . $suffix;
        $data['question'] = 'permits.page.year.question.' . $suffix;
        $data['browserTitle'] = 'permits.page.year.browser.title.' . $suffix;

        $data['guidance'] = [
            'value' => 'permits.page.year.ecmt-short-term.guidance',
            'disableHtmlEscape' => true,
        ];

        return $data;
    }
}
