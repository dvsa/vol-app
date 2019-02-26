<?php

namespace Permits\Data\Mapper;

use Common\RefData;
use Common\Util\Escape;
use Permits\View\Helper\EcmtSection;

/**
 *
 * Check Answers mapper
 */
class CheckAnswers
{
    public static function mapForDisplay(array $data)
    {
        $emissionsCategory = $data['windows']['windows'][0]['emissionsCategory']['id'];
        $euroEmissionsLabel = 'permits.form.euro6.label';
        $restrictedCountriesLabel = 'permits.page.restricted-countries.question';

        if ($emissionsCategory === RefData::EMISSIONS_CATEGORY_EURO5) {
            $euroEmissionsLabel = 'permits.form.euro5.label';
            $restrictedCountriesLabel = 'permits.form.restricted.countries.euro5.label';
        }

        $questions = [
            'permits.check-answers.page.question.licence',
            $euroEmissionsLabel,
            'permits.form.cabotage.label',
            $restrictedCountriesLabel,
            'permits.page.permits.required.question',
            'permits.page.number-of-trips.question',
            'permits.page.international.journey.question',
            'permits.page.sectors.question'
        ];

        $routes = [
            EcmtSection::ROUTE_LICENCE,
            EcmtSection::ROUTE_ECMT_EURO_EMISSIONS,
            EcmtSection::ROUTE_ECMT_CABOTAGE,
            EcmtSection::ROUTE_ECMT_COUNTRIES,
            EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
            EcmtSection::ROUTE_ECMT_TRIPS,
            EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
            EcmtSection::ROUTE_ECMT_SECTORS
        ];

        $countries = [];

        foreach ($data['application']['countrys'] as $country) {
            $countries[] = $country['countryDesc'];
        }

        if (empty($countries) && $data['application']['windowEmissionsCategory'] == RefData::EMISSIONS_CATEGORY_EURO6) {
            $restrictedCountries = 'No';
        } elseif (empty($countries) && $data['application']['windowEmissionsCategory'] == RefData::EMISSIONS_CATEGORY_EURO5) {
            $restrictedCountries = 'Yes';
        } else {
            $restrictedCountries = ['Yes', implode(', ', $countries)];
        }

        $answersFormatted = [
            [
                Escape::html($data['application']['licence']['licNo']),
                Escape::html($data['application']['licence']['trafficArea']['name']),
            ],
            $data['application']['emissions'] ? 'Yes' : 'No',
            $data['application']['cabotage'] ? 'Yes' : 'No',
            $restrictedCountries,
            $data['application']['permitsRequired'],
            $data['application']['trips'],
            $data['application']['internationalJourneys']['description'],
            $data['application']['sectors']['name']
        ];

        foreach ($questions as $index => $question) {
            $answers[] = [
                'question' => $question,
                'route' => $routes[$index],
                'answer' => $answersFormatted[$index]
            ];
        }

        return [
            'canCheckAnswers' => $data['application']['canCheckAnswers'],
            'answers' => $answers,
            'applicationRef' => $data['application']['applicationRef']
        ];
    }
}
