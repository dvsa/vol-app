<?php

namespace Permits\Data\Mapper;

use Common\Exception\ResourceNotFoundException;
use Permits\View\Helper\IrhpApplicationSection as Section;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Zend\Mvc\Controller\Plugin\Url;

class IrhpCheckAnswers
{
    /**
     * Maps data
     *
     * @param array                    $data       Array of data retrieved from the backend
     * @param TranslationHelperService $translator Translation service
     * @param Url                      $url        Url plugin
     *
     * @return array
     * @throw ResourceNotFoundException
     * @throw \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        if (empty($data)) {
            throw new ResourceNotFoundException('No IRHP answers found');
        }

        $countries = [];
        $noOfPermits = [];

        foreach ($data['irhpPermitApplications'] as $application) {
            $permitsRequired = $application['permitsRequired'];
            $validToYear = date('Y', strtotime($application['irhpPermitWindow']['irhpPermitStock']['validTo']));

            if (isset($application['irhpPermitWindow']['irhpPermitStock']['country'])) {
                $countryDesc = $translator->translate(
                    $application['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc']
                );

                $countries[] = $countryDesc;

                $noOfPermits[] = $translator->translateReplace(
                    'permits.check-your-answers.countries',
                    [
                        $permitsRequired,
                        $countryDesc,
                        $validToYear
                    ]
                );
            } else {
                $noOfPermits[] = $translator->translateReplace(
                    'permits.check-your-answers.no-of-permits',
                    [
                        $permitsRequired,
                        $validToYear
                    ]
                );
            }
        }

        switch ($data['irhpPermitType']['id']) {
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
                // populate $answers with bilateral variation
                $answers = [
                    [
                        'question' => 'permits.page.fee.permit.type',
                        'route' => null,
                        'answer' => $data['irhpPermitType']['name']['description']
                    ],
                    [
                        'question' => 'permits.check-answers.page.question.licence',
                        'route' => Section::ROUTE_LICENCE,
                        'answer' => [ $data['licence']['licNo'], $data['licence']['trafficArea']['name'] ]
                    ],
                    [
                        'question' => 'permits.irhp.application.question.countries',
                        'route' => Section::ROUTE_COUNTRIES,
                        'answer' => implode(array_unique($countries), ', ')
                    ],
                    [
                        'question' => 'permits.irhp.application.question.no-of-permits',
                        'route' => Section::ROUTE_NO_OF_PERMITS,
                        'answer' => $noOfPermits
                    ]
                ];
                break;
            case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
                // populate $answers with multilateral variation
                $answers = [
                    [
                        'question' => 'permits.page.fee.permit.type',
                        'route' => null,
                        'answer' => $data['irhpPermitType']['name']['description']
                    ],
                    [
                        'question' => 'permits.check-answers.page.question.licence',
                        'route' => Section::ROUTE_LICENCE,
                        'answer' => [ $data['licence']['licNo'], $data['licence']['trafficArea']['name'] ]
                    ],
                    [
                        'question' => 'permits.irhp.application.question.no-of-permits',
                        'route' => Section::ROUTE_NO_OF_PERMITS,
                        'answer' => $noOfPermits
                    ]
                ];
                break;
            default:
                throw new \RuntimeException('This mapper only supports bilateral and multilateral');
        }

        return [
            'canCheckAnswers' => $data['canCheckAnswers'],
            'answers' => $answers,
            'applicationRef' => $data['applicationRef']
        ];
    }
}
