<?php

namespace Permits\Controller;

use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class CancelIrhpApplicationController extends AbstractSelfserveController
{
    public const CABOTAGE_SLUG_WHITELIST = ['bi-cabotage-only', 'bi-standard-and-cabotage'];
    public const IRHP_APPLICATION_SLUG_WHITELIST = ['check-ecmt-needed'];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP,
    ];

    protected $conditionalDisplayConfig = [
        'cancel' => ConditionalDisplayConfig::IRHP_APP_CAN_BE_CANCELLED,
        'confirmation' => ConditionalDisplayConfig::IRHP_APP_IS_CANCELLED,
    ];

    protected $formConfig = [
        'cancel' => FormConfig::FORM_CANCEL_IRHP_APP,
    ];

    protected $templateConfig = [
        'cancel' => 'permits/single-question',
        'confirmation' => 'permits/confirmation',
    ];

    protected $templateVarsConfig = [
        'cancel' => [
            'browserTitle' => 'permits.page.cancel.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'question' => 'permits.page.cancel.question',
            'bulletList' => [
                'title' => 'permits.page.cancel.bullet.list.title',
                'type' => 'medium',
                'list' => 'markup-irhp-application-cancel'
            ],
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW
        ],
        'confirmation' => [
            'browserTitle' => 'permits.page.confirmation.cancel.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'title' => 'permits.page.confirmation.cancel.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title.now',
                'list' => 'markup-irhp-cancel-confirmation'
            ]
        ],
    ];

    protected $postConfig = [
        'cancel' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'command' => CancelApplication::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_CANCEL_CONFIRMATION,
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
        MapperManager $mapperManager
    ) {
        parent::__construct($translationHelper, $formHelper, $tableBuilder, $mapperManager);
    }

    /**
     * @return void
     */
    #[\Override]
    public function mergeTemplateVars()
    {
        if (isset($this->queryParams['fromBilateralCabotage'])) {
            $this->handleCabotageBacklink();
        } elseif (isset($this->queryParams['fromCountries'])) {
            $this->templateVarsConfig['cancel']['backUri'] = IrhpApplicationSection::ROUTE_COUNTRIES;
        } elseif (isset($this->queryParams['fromIrhpApplication'])) {
            $this->handleIrhpApplicationBackLink();
        }

        parent::mergeTemplateVars();
    }

    /**
     * @return void
     */
    private function handleCabotageBacklink()
    {
        if (!isset($this->queryParams['slug']) || !isset($this->queryParams['ipa'])) {
            return;
        }

        $slug = $this->queryParams['slug'];
        if (!in_array($slug, self::CABOTAGE_SLUG_WHITELIST)) {
            return;
        }

        $this->templateVarsConfig['cancel']['backUri'] = IrhpApplicationSection::ROUTE_IPA_QUESTION;
        $this->templateVarsConfig['cancel']['backUriParams'] = [
            'irhpPermitApplication' => $this->queryParams['ipa'],
            'slug' => $slug
        ];
    }

    /**
     * @return void
     */
    private function handleIrhpApplicationBackLink()
    {
        $slug = $this->queryParams['fromIrhpApplication'];

        if (!in_array($slug, self::IRHP_APPLICATION_SLUG_WHITELIST)) {
            return;
        }

        $this->templateVarsConfig['cancel']['backUri'] = IrhpApplicationSection::ROUTE_QUESTION;
        $this->templateVarsConfig['cancel']['backUriParams'] = [
            'slug' => $slug
        ];
    }
}
