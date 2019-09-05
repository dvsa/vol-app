<?php

namespace Permits\Data\Mapper;

use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Util\Escape;
use Permits\View\Helper\EcmtSection;
use Zend\Mvc\Controller\Plugin\Url;

/**
 *
 * Check Answers mapper
 */
class CheckAnswers
{
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
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
            $translator->translateReplace(
                'permits.check-your-answers.no-of-permits.year',
                [$year]
            )
        ) . '</strong>';

        $permitsRequired = array_merge(
            [$permitsRequiredYearHeading],
            EcmtNoOfPermits::mapForDisplay($data['application'], $translator, $url)
        );

        $answers = [
            IrhpCheckAnswers::permitTypeAnswer($data['application']['permitType']['description']),
            IrhpCheckAnswers::licenceAnswer($data['application']['licence'], EcmtSection::ROUTE_LICENCE),
            IrhpCheckAnswers::answer(
                'permits.form.cabotage.label',
                $data['application']['cabotage'],
                EcmtSection::ROUTE_ECMT_CABOTAGE,
                RefData::QUESTION_TYPE_BOOLEAN
            ),
            IrhpCheckAnswers::answer(
                'permits.page.roadworthiness.question',
                $data['application']['roadworthiness'],
                EcmtSection::ROUTE_ECMT_ROADWORTHINESS,
                RefData::QUESTION_TYPE_BOOLEAN
            ),
            IrhpCheckAnswers::answer(
                'permits.page.restricted-countries.question',
                $restrictedCountries,
                EcmtSection::ROUTE_ECMT_COUNTRIES,
                RefData::QUESTION_TYPE_STRING
            ),
            IrhpCheckAnswers::answer(
                'permits.form.euro-emissions.label',
                $data['application']['emissions'],
                EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
                RefData::QUESTION_TYPE_BOOLEAN
            ),
            IrhpCheckAnswers::answer(
                'permits.page.permits.required.question',
                $permitsRequired,
                EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
                RefData::QUESTION_TYPE_STRING,
                [],
                [],
                false
            ),
            IrhpCheckAnswers::answer(
                'permits.page.number-of-trips.question',
                $data['application']['trips'],
                EcmtSection::ROUTE_ECMT_TRIPS,
                RefData::QUESTION_TYPE_INTEGER
            ),
            IrhpCheckAnswers::answer(
                'permits.page.international.journey.question',
                $data['application']['internationalJourneys']['description'],
                EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
                RefData::QUESTION_TYPE_STRING
            ),
            IrhpCheckAnswers::answer(
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
}
