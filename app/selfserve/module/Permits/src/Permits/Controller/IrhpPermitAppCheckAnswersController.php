<?php

namespace Permits\Controller;

use Common\Preference\Language;
use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCheckAnswers;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\AnswersSummary;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpPermitAppCheckAnswersController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP_CHECK_ANSWERS,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_IPA_CAN_CHECK_ANSWERS,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_IRHP_IPA_CHECK_ANSWERS,
    ];

    protected $templateConfig = [
        'default' => 'permits/irhp-permit-app-check-answers'
    ];

    protected $templateVarsConfig = [
        'default' => [
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'browserTitle' => 'permits.page.check-answers.browser.title',
            'title' => 'permits.page.check-answers.title',
        ]
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'command' => UpdateCheckAnswers::class,
            'params' => [
                'route' => [
                    'id',
                    'irhpPermitApplication',
                ],
            ],
            'step' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'saveAndReturnStep' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];

    /**
     * @param TranslationHelperService $translationHelper
     * @param FormHelperService $formHelper
     * @param TableFactory $tableBuilder
     * @param MapperManager $mapperManager
     */
    public function __construct(
        TranslationHelperService $translationHelper,
        FormHelperService $formHelper,
        TableFactory $tableBuilder,
        MapperManager $mapperManager,
        protected Language $languagePreference
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @return void
     */
    #[\Override]
    public function mergeTemplateVars()
    {
        $backUri = IrhpApplicationSection::ROUTE_IPA_QUESTION;
        $backUriParams = [
            'slug' => RefData::BILATERAL_NUMBER_OF_PERMITS,
        ];

        if (isset($this->queryParams['fromOverview'])) {
            $backUri = IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW;
            $backUriParams = [];
        }

        $this->templateVarsConfig['default']['backUri'] = $backUri;
        $this->templateVarsConfig['default']['backUriParams'] = $backUriParams;

        parent::mergeTemplateVars();
    }

    /**
     * @return void
     */
    #[\Override]
    public function retrieveData()
    {
        parent::retrieveData();

        $irhpApplicationId = $this->data[IrhpAppDataSource::DATA_KEY]['id'];

        $irhpPermitApplicationId = $this->data['routeParams']['irhpPermitApplication'];

        $translateToWelsh = $this->languagePreference == Language::OPTION_CY ? 'Y' : 'N';

        $answersSummaryParams = [
            'id' => $irhpApplicationId,
            'irhpPermitApplication' => $irhpPermitApplicationId,
            'translateToWelsh' => $translateToWelsh
        ];

        $response = $this->handleQuery(
            AnswersSummary::create($answersSummaryParams)
        );

        $result = $response->getResult();

        $this->data['rows'] = $result['rows'];

        foreach ($this->data[IrhpAppDataSource::DATA_KEY]['irhpPermitApplications'] as $ipa) {
            if ($ipa['id'] == $irhpPermitApplicationId) {
                $country = $ipa['irhpPermitWindow']['irhpPermitStock']['country'];
                $this->data[IrhpAppDataSource::DATA_KEY]['countryId'] = $country['id'];
                $this->data[IrhpAppDataSource::DATA_KEY]['countryName'] = $country['countryDesc'];
                break;
            }
        }
    }
}
