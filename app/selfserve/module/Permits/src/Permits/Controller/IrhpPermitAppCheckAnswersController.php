<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\Preference\Language;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateCheckAnswers;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\AnswersSummary;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;

use Permits\View\Helper\IrhpApplicationSection;

class IrhpPermitAppCheckAnswersController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

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

    public function retrieveData()
    {
        parent::retrieveData();

        $irhpApplicationId = $this->data[IrhpAppDataSource::DATA_KEY]['id'];

        $irhpPermitApplicationId = $this->data['routeParams']['irhpPermitApplication'];

        $languagePreference = $this->getServiceLocator()
            ->get('LanguagePreference')
            ->getPreference();

        $translateToWelsh = $languagePreference == Language::OPTION_CY ? 'Y' : 'N';

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
