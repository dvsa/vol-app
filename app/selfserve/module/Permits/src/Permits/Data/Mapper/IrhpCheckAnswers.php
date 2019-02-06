<?php

namespace Permits\Data\Mapper;

use Common\Exception\ResourceNotFoundException;
use Permits\View\Helper\IrhpApplicationSection as Section;

class IrhpCheckAnswers
{
    public static function mapForDisplay(array $data)
    {
        if (empty($data)) {
            throw new ResourceNotFoundException('No IRHP answers found');
        }
        $questions = [
            'permits.page.fee.permit.type',
            'permits.check-answers.page.question.licence',
            'permits.irhp.application.question.countries',
            'permits.irhp.application.question.no-of-permits'
        ];

        $routes = [
            null,
            Section::ROUTE_LICENCE,
            Section::ROUTE_COUNTRIES,
            Section::ROUTE_NO_OF_PERMITS
        ];

        $countries = [];
        $noOfPermits = [];
        foreach ($data['irhpPermitApplications'] as $application) {
            $countries[] = $application['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc'];
            $noOfPermits[] = $application['permitsRequired'] .
                                ' permits for ' .
                                $application['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc'] .
                                ' in ' .
                                date('Y', strtotime($application['irhpPermitWindow']['irhpPermitStock']['validTo']));
        }

        $answersFormatted = [
            $data['irhpPermitType']['name']['description'],
            [
                $data['licence']['licNo'],
                $data['licence']['trafficArea']['name'],
            ],
            implode(array_unique($countries), ', '),
            $noOfPermits
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
