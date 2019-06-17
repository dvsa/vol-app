<?php

namespace Permits\Data\Mapper;

use Common\Exception\ResourceNotFoundException;
use Permits\View\Helper\IrhpApplicationSection as Section;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Zend\Mvc\Controller\Plugin\Url;

class IrhpCheckAnswers
{
    const SKIPPED_QUESTIONS = [
        'custom-licence',
        'custom-check-answers',
        'custom-declaration',
    ];

    /**
     * Maps data
     *
     * @param array                    $data       Array of data retrieved from the backend
     * @param TranslationHelperService $translator Translation service
     * @param Url                      $url        Url plugin
     *
     * @return array
     * @throws ResourceNotFoundException
     * @throws \RuntimeException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public static function mapForDisplay(array $data, TranslationHelperService $translator, Url $url)
    {
        if (empty($data)) {
            throw new ResourceNotFoundException('No IRHP answers found');
        }

        $countries = [];
        $noOfPermits = [];

        switch ($data['irhpPermitType']['id']) {
            case RefData::ECMT_REMOVAL_PERMIT_TYPE_ID:
                $noOfPermits = [$data['permitsRequired']];
                break;
            default:
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
        }

        $openingAnswers = static::openingAnswers($data);
        $extraAnswers = [];

        switch ($data['irhpPermitType']['id']) {
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
                // populate $answers with bilateral variation
                $extraAnswers = [
                    static::answer(
                        'permits.irhp.application.question.countries',
                        implode(array_unique($countries), ', '),
                        Section::ROUTE_COUNTRIES
                    ),
                    static::permitsRequiredAnswer($noOfPermits),
                ];
                break;
            case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
                // populate $answers with multilateral variation
                $extraAnswers = [
                    static::permitsRequiredAnswer($noOfPermits),
                ];
                break;
            default:
                foreach ($data['questionAnswerData'] as $answerData) {
                    //licence, check answers, declaration and so on are skipped
                    if (in_array($answerData['slug'], static::SKIPPED_QUESTIONS)) {
                        continue;
                    }

                    $params = ['slug' => $answerData['slug']];

                    //a special case is made for number of permits - sometimes there may be multiple countries/years
                    if ($answerData['slug'] === 'number-of-permits') {
                        $extraAnswers[] = static::permitsRequiredAnswer($noOfPermits, Section::ROUTE_QUESTION, $params);
                        continue;
                    }

                    $extraAnswers[] = static::answer(
                        $answerData['question'],
                        $answerData['answer'],
                        Section::ROUTE_QUESTION,
                        $answerData['questionType'],
                        $params
                    );
                }
        }

        return [
            'canCheckAnswers' => $data['canCheckAnswers'],
            'answers' => array_merge($openingAnswers, $extraAnswers),
            'applicationRef' => $data['applicationRef']
        ];
    }

    /**
     * Answer data for a permits required row on check answers page
     *
     * @param array  $noOfPermits number of permits required (sometimes multiple years/countries so multiple lines)
     * @param string $route       route used to change the answer
     * @param array  $params      route params
     *
     * @return array
     */
    private static function permitsRequiredAnswer(
        array $noOfPermits,
        string $route = Section::ROUTE_NO_OF_PERMITS,
        array $params = []
    ) {
        return static::answer(
            'permits.irhp.application.question.no-of-permits',
            $noOfPermits,
            $route,
            null,
            $params
        );
    }

    /**
     * Answer data for a permit type row on check answers page
     *
     * @param string $permitType permit type
     *
     * @return array
     */
    private static function permitTypeAnswer(string $permitType): array
    {
        return static::answer('permits.page.fee.permit.type', $permitType);
    }

    /**
     * Answer data for a licence row on check answers page
     *
     * @param array $licence licence data
     *
     * @return array
     */
    private static function licenceAnswer(array $licence): array
    {
        $answer = [
            $licence['licNo'],
            $licence['trafficArea']['name']
        ];

        return static::answer('permits.check-answers.page.question.licence', $answer, Section::ROUTE_LICENCE);
    }

    /**
     * Opening answers common to all permit types
     *
     * @param array $data answer data
     *
     * @return array
     */
    private static function openingAnswers(array $data): array
    {
        return [
            static::permitTypeAnswer($data['irhpPermitType']['name']['description']),
            static::licenceAnswer($data['licence'])
        ];
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
     *
     * @return array
     */
    private static function answer(
        string $question,
        $answer,
        string $route = null,
        string $questionType = null,
        array $params = [],
        array $options = []
    ): array {
        return [
            'question' => $question,
            'route' => $route,
            'answer' => $answer,
            'questionType' => $questionType,
            'params' => $params,
            'options' => $options,
        ];
    }
}
