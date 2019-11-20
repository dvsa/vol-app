<?php

namespace Permits\Data\Mapper;

use Common\Exception\ResourceNotFoundException;
use Common\RefData;
use Common\Service\Helper\TranslationHelperService;
use Common\Util\Escape;
use Permits\View\Helper\IrhpApplicationSection as Section;

class IrhpCheckAnswers
{
    const SKIPPED_QUESTIONS = [
        'custom-licence',
        'custom-check-answers',
        'custom-declaration',
    ];

    const REQUIRED_PERMITS_SLUG = [
        'st-number-of-permits',
        'number-of-permits',
    ];

    const SECTORS_SLUG = [
        'st-sectors',
    ];

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
     * @return IrhpCheckAnswers
     */
    public function __construct(TranslationHelperService $translator, EcmtNoOfPermits $ecmtNoOfPermits)
    {
        $this->translator = $translator;
        $this->ecmtNoOfPermits = $ecmtNoOfPermits;
    }

    /**
     * Maps data
     *
     * @param array $data Array of data retrieved from the backend
     *
     * @return array
     *
     * @throws ResourceNotFoundException
     */
    public function mapForDisplay(array $data)
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
            case RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID:
                $irhpPermitStock = $data['irhpPermitApplications'][0]['irhpPermitWindow']['irhpPermitStock'];
                $validityYear = $irhpPermitStock['validityYear'];

                if ($validityYear == 2019) {
                    $permitsRequiredYearContent = $this->translator->translateReplace(
                        'permits.check-your-answers.no-of-permits.year',
                        [$validityYear]
                    );
                } else {
                    $permitsRequiredYearContent = $this->translator->translate($irhpPermitStock['periodNameKey']);
                }

                $permitsRequiredYearMarkup = '<strong>' . Escape::html($permitsRequiredYearContent) . '</strong>';

                $noOfPermits = array_merge(
                    [$permitsRequiredYearMarkup],
                    $this->ecmtNoOfPermits->mapForDisplay($data['irhpPermitApplications'][0])
                );
                break;
            default:
                foreach ($data['irhpPermitApplications'] as $application) {
                    $permitsRequired = $application['permitsRequired'];
                    $validToYear = date('Y', strtotime($application['irhpPermitWindow']['irhpPermitStock']['validTo']));

                    if (isset($application['irhpPermitWindow']['irhpPermitStock']['country'])) {
                        $countryDesc = $this->translator->translate(
                            $application['irhpPermitWindow']['irhpPermitStock']['country']['countryDesc']
                        );

                        $countries[] = $countryDesc;

                        $noOfPermits[] = $this->translator->translateReplace(
                            'permits.check-your-answers.countries',
                            [
                                $permitsRequired,
                                $countryDesc,
                                $validToYear
                            ]
                        );
                    } else {
                        $noOfPermits[] = $this->translator->translateReplace(
                            'permits.check-your-answers.no-of-permits',
                            [
                                $permitsRequired,
                                $validToYear
                            ]
                        );
                    }
                }
        }

        $openingAnswers = $this->openingAnswers($data);
        $extraAnswers = [];

        switch ($data['irhpPermitType']['id']) {
            case RefData::IRHP_BILATERAL_PERMIT_TYPE_ID:
                // populate $answers with bilateral variation
                $extraAnswers = [
                    $this->answer(
                        'permits.irhp.application.question.countries',
                        implode(array_unique($countries), ', '),
                        Section::ROUTE_COUNTRIES
                    ),
                    $this->permitsRequiredAnswer($noOfPermits),
                ];
                break;
            case RefData::IRHP_MULTILATERAL_PERMIT_TYPE_ID:
                // populate $answers with multilateral variation
                $extraAnswers = [
                    $this->permitsRequiredAnswer($noOfPermits),
                ];
                break;
            default:
                foreach ($data['questionAnswerData'] as $answerData) {
                    //licence, check answers, declaration and so on are skipped
                    if (in_array($answerData['slug'], self::SKIPPED_QUESTIONS)) {
                        continue;
                    }

                    $params = ['slug' => $answerData['slug']];

                    //special case for number of permits - sometimes there may be multiple countries/years/emissions
                    if (in_array($answerData['slug'], self::REQUIRED_PERMITS_SLUG)) {
                        //permits required for short terms is pre-escaped due to containing HTML tags
                        $escape = true;

                        if ($data['irhpPermitType']['id'] === RefData::ECMT_SHORT_TERM_PERMIT_TYPE_ID) {
                            $escape = false;
                        }

                        $extraAnswers[] = $this->permitsRequiredAnswer(
                            $noOfPermits,
                            Section::ROUTE_QUESTION,
                            $params,
                            $escape
                        );
                        continue;
                    } elseif (in_array($answerData['slug'], self::SECTORS_SLUG)) {
                        $extraAnswers[] = $this->answer(
                            $answerData['question'],
                            $data['sectors']['name'],
                            Section::ROUTE_QUESTION,
                            $answerData['questionType'],
                            $params
                        );
                        continue;
                    }

                    $extraAnswers[] = $this->answer(
                        $answerData['question'],
                        $answerData['answer'],
                        Section::ROUTE_QUESTION,
                        $answerData['questionType'],
                        $params
                    );
                }
        }

        return [
            'prependTitle' => $data['irhpPermitType']['name']['description'],
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
     * @param bool   $escape      whether value should be escaped by the answer formatter
     *
     * @return array
     */
    private function permitsRequiredAnswer(
        array $noOfPermits,
        string $route = Section::ROUTE_NO_OF_PERMITS,
        array $params = [],
        bool $escape = true
    ) {
        return $this->answer(
            'permits.irhp.application.question.no-of-permits',
            $noOfPermits,
            $route,
            null,
            $params,
            [],
            $escape
        );
    }

    /**
     * Answer data for a permit type row on check answers page
     *
     * @param string $permitType permit type
     *
     * @return array
     */
    public function permitTypeAnswer(string $permitType): array
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
    public function licenceAnswer(array $licence, string $route = Section::ROUTE_LICENCE): array
    {
        $answer = [
            $licence['licNo'],
            $licence['trafficArea']['name']
        ];

        return $this->answer('permits.check-answers.page.question.licence', $answer, $route);
    }

    /**
     * Opening answers common to all permit types
     *
     * @param array $data answer data
     *
     * @return array
     */
    private function openingAnswers(array $data): array
    {
        return [
            $this->permitTypeAnswer($data['irhpPermitType']['name']['description']),
            $this->licenceAnswer($data['licence'])
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
     * @param bool        $escape       whether the value should be escaped by the answer formatter
     *
     * @return array
     */
    public function answer(
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
