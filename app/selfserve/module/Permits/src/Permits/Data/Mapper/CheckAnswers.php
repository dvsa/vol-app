<?php

namespace Permits\Data\Mapper;

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
        $questions = [
            'check-answers.page.question.licence',
            'check-answers.page.question.euro6',
            'check-answers.page.question.cabotage',
            'check-answers.page.question.restricted-countries',
            'check-answers.page.question.permits-required',
            'check-answers.page.question.trips',
            'check-answers.page.question.internationalJourneys',
            'check-answers.page.question.sector'
        ];

        $routes = [
            EcmtSection::ROUTE_ECMT_LICENCE,
            EcmtSection::ROUTE_ECMT_EURO6,
            EcmtSection::ROUTE_ECMT_CABOTAGE,
            EcmtSection::ROUTE_ECMT_COUNTRIES,
            EcmtSection::ROUTE_ECMT_NO_OF_PERMITS,
            EcmtSection::ROUTE_ECMT_TRIPS,
            EcmtSection::ROUTE_ECMT_INTERNATIONAL_JOURNEY,
            EcmtSection::ROUTE_ECMT_SECTORS
        ];

        $countries = [];

        foreach ($data['countrys'] as $country) {
            $countries[] = $country['countryDesc'];
        }

        $answersFormatted = [
            [
                Escape::html($data['licence']['licNo']),
                Escape::html($data['licence']['trafficArea']['name']),
            ],
            $data['emissions'] ? 'Yes' : 'No',
            $data['cabotage'] ? 'Yes' : 'No',
            empty($countries) ? 'No' : ['Yes', implode(', ', $countries)],
            $data['permitsRequired'],
            $data['trips'],
            $data['internationalJourneys']['description'],
            $data['sectors']['name']
        ];

        foreach ($questions as $index => $question) {
            $answers[] = [
                'question' => $question,
                'route' => $routes[$index],
                'answer' => $answersFormatted[$index]
            ];
        }

        return [
            'canCheckAnswers' => $data['canCheckAnswers'],
            'answers' => $answers,
            'applicationRef' => $data['applicationRef']
        ];
    }
}
