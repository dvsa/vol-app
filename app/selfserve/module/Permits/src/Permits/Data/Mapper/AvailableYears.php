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
    const ERR_UNSUPPORTED = 'This mapper currently only supports ECMT short term';

    /**
     * @param array $data
     * @param Form  $form
     * @param TranslationHelperService $translator
     *
     * @return array
     */
    public static function mapForFormOptions(array $data, $form, TranslationHelperService $translator)
    {
        if ($data['type'] != RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID) {
            throw new RuntimeException(self::ERR_UNSUPPORTED);
        }

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

        if (count($years) > 1) {
            $data['hint'] = 'permits.page.year.hint.multiple-years-available';
            $data['question'] = 'permits.page.year.question.multiple-years-available';
            $data['browserTitle'] = 'permits.page.year.browser.title.multiple-years-available';
        }

        return $data;
    }
}
