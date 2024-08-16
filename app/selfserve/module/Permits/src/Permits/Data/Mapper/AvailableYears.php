<?php

namespace Permits\Data\Mapper;

use Common\Data\Mapper\MapperInterface;
use Common\Form\Elements\Types\Html;
use Common\Form\Form;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Permits\Controller\Config\DataSource\AvailableYears as AvailableYearsDataSource;
use RuntimeException;
use Laminas\Form\Element\Hidden;

/**
 * Available years mapper
 */
class AvailableYears implements MapperInterface
{
    use MapFromResultTrait;

    /**
     * Create service instance
     *
     *
     * @return AvailableYears
     */
    public function __construct(private TranslationHelperService $translator)
    {
    }

    /**
     * @param Form  $form
     * @return array
     */
    public function mapForFormOptions(array $data, $form)
    {
        return match ($data['type']) {
            RefData::ECMT_PERMIT_TYPE_ID => $this->mapForEcmtAnnual($data, $form),
            RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID => $this->mapForEcmtShortTerm($data, $form),
            default => throw new RuntimeException('This mapper does not support permit type ' . $data['type']),
        };
    }

    /**
     * Map year options for ECMT annual permit type
     *
     * @param Form  $form
     * @return array
     */
    private function mapForEcmtAnnual(array $data, $form)
    {
        return $this->mapAvailableYears($data, $form, 'permits.page.year.ecmt-annual');
    }

    /**
     * Map year options for ECMT short term permit type
     *
     * @param Form  $form
     * @return array
     */
    private function mapForEcmtShortTerm(array $data, $form)
    {
        $translationPrefix = 'permits.page.year.ecmt-short-term';

        $data = $this->mapAvailableYears($data, $form, $translationPrefix);

        $data['guidance'] = [
            'value' => sprintf('%s.guidance', $translationPrefix),
            'disableHtmlEscape' => true,
        ];

        return $data;
    }

    /**
     * Map available year options
     *
     * @param Form  $form
     * @param string $translationPrefix
     * @return array
     */
    private function mapAvailableYears(array $data, $form, $translationPrefix)
    {
        $availableYearsData = $data[AvailableYearsDataSource::DATA_KEY];
        $years = $availableYearsData['years'];
        $selectedYear = $availableYearsData['selectedYear'];

        if (count($years) == 1) {
            $yearsKeys = array_keys($years);
            $firstOptionKey = $yearsKeys[0];

            $this->singleOption($form, $years[$firstOptionKey], $translationPrefix);

            $data['question'] = sprintf('%s.question.one-year-available', $translationPrefix);
        } else {
            $this->multipleOptions($form, $years, $selectedYear);

            $data['question'] = sprintf('%s.question.multiple-years-available', $translationPrefix);
            $data['hint'] = sprintf('%s.hint.multiple-years-available', $translationPrefix);
        }

        // make title the same as question
        $data['browserTitle'] = $data['question'];

        return $data;
    }

    /**
     * @param int $year
     * @param string $translationPrefix
     */
    private function singleOption(Form $form, $year, $translationPrefix): void
    {
        $markup = sprintf(
            '<p class="govuk-body-l">%s</p>',
            $this->translator->translateReplace(
                sprintf('%s.hint.one-year-available', $translationPrefix),
                [$year]
            )
        );

        $fields = $form->get('fields');

        // Add label for single option
        $fields->add(
            [
                'name' => 'yearLabel',
                'type' => Html::class,
                'attributes' => [
                    'value' => $markup,
                ]
            ]
        );

        // add hidden field with id
        $fields->add(
            [
                'name' => 'year',
                'type' => Hidden::class,
                'attributes' => [
                    'value' => $year,
                ]
            ]
        );
    }

    /**
     * @param array $years
     * @param string $selectedYear
     */
    private function multipleOptions(Form $form, $years, $selectedYear): void
    {
        $valueOptions = [];

        foreach ($years as $year) {
            $valueOptions[] = [
                'value' => $year,
                'label' => $year,
                'selected' => $year == $selectedYear
            ];
        }

        $form->get('fields')->get('year')->setValueOptions(
            $this->transformValueOptions($valueOptions)
        );
    }

    /**
     * Set the id of the first radio button in the list for validation accessibility purposes
     *
     *
     * @return array
     */
    private function transformValueOptions(array $valueOptions)
    {
        if (count($valueOptions)) {
            $valueOptions[0]['attributes'] = [
                'id' => 'year'
            ];
        }

        return $valueOptions;
    }
}
