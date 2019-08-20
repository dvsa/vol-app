<?php

namespace Permits\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Util\Escape;
use Permits\View\Helper\EcmtSection;
use Zend\Mvc\Controller\Plugin\Url;

/**
 * Check Answers mapper
 *
 * TODO: this is expected to be redundant following the EMCT->IRHP migration
 */
class CheckAnswers
{
    /** @var TranslationHelperService */
    private $translator;

    /** @var EcmtNoOfPermits */
    private $ecmtNoOfPermits;

    /**
     * Create service instance
     *
     * @param TranslationHelperService $translator
     * @param EcmtNoOfPermits $ecmtNoOfPermits
     *
     * @return CheckAnswers
     */
    public function __construct(
        TranslationHelperService $translator,
        EcmtNoOfPermits $ecmtNoOfPermits
    ) {
        $this->translator = $translator;
        $this->ecmtNoOfPermits = $ecmtNoOfPermits;
    }

    public function mapForDisplay(array $data)
    {
        $restrictedCountries = 'No';

        if ($data['application']['hasRestrictedCountries']) {
            $countries = [];

            foreach ($data['application']['countrys'] as $country) {
                $countries[] = $country['countryDesc'];
            }

            $restrictedCountries = ['Yes', implode(', ', $countries)];
        }

        $year = date(
            'Y',
            strtotime(
                $data['application']['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock']['validTo']
            )
        );

        $permitsRequiredYearHeading = '<strong>' . Escape::html(
            $this->translator->translateReplace(
                'permits.check-your-answers.no-of-permits.year',
                [$year]
            )
        ) . '</strong>';

        $permitsRequired = array_merge(
            [$permitsRequiredYearHeading],
            $this->ecmtNoOfPermits->mapForDisplay($data['application'])
        );

        $answers = [
            $this->permitTypeAnswer($data['application']['permitType']['description']),
            $this->licenceAnswer($data['application']['licence'], EcmtSection::ROUTE_LICENCE),
            $this->answer(
                'permits.form.cabotage.label',
                $data['application']['cabotage'],
                EcmtSection::ROUTE_ECMT_CABOTAGE,
                RefData::QUESTION_TYPE_BOOLEAN
            ),
            $this->answer(
                'permits.page.roadworthiness.question',
                $data['application']['roadworthiness'],
                EcmtSection::ROUTE_ECMT_ROADWORTHINESS,
                RefData::QUESTION_TYPE_BOOLEAN
            ),
            $this->answer(
                'permits.page.restricted-countries.question',
                $restrictedCountries,
                EcmtSection::ROUTE_ECMT_COUNTRIES,
                RefData::QUESTION_TYPE_STRING
            ),
            $this->answer(
                'permits.form.euro-emissions.label',
                $data['application']['emissions'],
                EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
                RefData::QUESTION_TYPE_BOOLEAN
            ),
            $this->answer(
                'permits.page.permits.required.question',
                $permitsRequired,
                EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
                RefData::QUESTION_TYPE_STRING,
                [],
                [],
                false
            ),
            $this->answer(
                'permits.page.number-of-trips.question',
                $data['application']['trips'],
                EcmtSection::ROUTE_ECMT_TRIPS,
                RefData::QUESTION_TYPE_INTEGER
            ),
            $this->answer(
                'permits.page.international.journey.question',
                $data['application']['internationalJourneys']['description'],
                EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
                RefData::QUESTION_TYPE_STRING
            ),
            $this->answer(
                'permits.page.sectors.question',
                $data['application']['sectors']['name'],
                EcmtSection::ROUTE_ECMT_SECTORS,
                RefData::QUESTION_TYPE_STRING
            ),
        ];

        return [
            'canCheckAnswers' => $data['application']['canCheckAnswers'],
            'answers' => $answers,
            'applicationRef' => $data['application']['applicationRef']
        ];
    }

    /**
     * Answer data for a permit type row on check answers page
     *
     * @param string $permitType permit type
     *
     * @return array
     */
    private function permitTypeAnswer(string $permitType): array
    {
        return $this->answer('permits.page.fee.permit.type', $permitType);
    }

    /**
     * Answer data for a licence row on check answers page
     *
     * @param array  $licence licence data
     * @param string $route   licence page route (allows override for legacy ECMT)
     *
     * @return array
     */
    private function licenceAnswer(array $licence, string $route = Section::ROUTE_LICENCE): array
    {
        $answer = [
            $licence['licNo'],
            $licence['trafficArea']['name']
        ];

        return $this->answer('permits.check-answers.page.question.licence', $answer, $route);
    }

    /**
     * Array of data to build a check answers row
     *
     * @param string      $question     the question (translation key)
     * @param mixed       $answer       the answer
     * @param string|null $route        route to change the answer
     * @param string|null $questionType the type of question
     * @param array       $params       route params
     * @param array       $options      route options
     * @param bool        $escape       whether the value should be escaped by the answer formatter
     *
     * @return array
     */
    private function answer(
        string $question,
        $answer,
        string $route = null,
        string $questionType = null,
        array $params = [],
        array $options = [],
        bool  $escape = true
    ): array {
        return [
            'question' => $question,
            'route' => $route,
            'answer' => $answer,
            'questionType' => $questionType,
            'params' => $params,
            'options' => $options,
            'escape' => $escape,
        ];
    }
}
