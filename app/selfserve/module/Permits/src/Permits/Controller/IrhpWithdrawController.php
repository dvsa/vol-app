<?php

namespace Permits\Controller;

use Common\RefData;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Withdraw;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\Data\Mapper\MapperManager;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpWithdrawController extends AbstractSelfserveController
{
    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP,
    ];

    protected $conditionalDisplayConfig = [
        'withdraw' => ConditionalDisplayConfig::IRHP_APP_CAN_BE_WITHDRAWN,
        'confirmation' => ConditionalDisplayConfig::IRHP_APP_IS_WITHDRAWN,
    ];

    protected $formConfig = [
        'withdraw' => FormConfig::FORM_WITHDRAW_PERMIT_APP,
    ];

    protected $templateConfig = [
        'withdraw' => 'permits/single-question',
        'confirmation' => 'permits/confirmation',
    ];

    protected $templateVarsConfig = [
        'withdraw' => [
            'browserTitle' => 'permits.page.withdraw.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'question' => 'permits.page.withdraw.question',
            'bulletList' => [
                'title' => 'permits.page.withdraw.bullet.list.title',
                'type' => 'medium',
                'list' => 'markup-ecmt-application-withdraw',
            ],
            'backUri' => IrhpApplicationSection::ROUTE_UNDER_CONSIDERATION,
        ],
        'confirmation' => [
            'browserTitle' => 'permits.page.confirmation.withdraw.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'title' => 'permits.page.confirmation.withdraw.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title.now',
                'list' => 'markup-ecmt-withdraw-confirmation',
            ],
        ],
    ];

    protected $postConfig = [
        'withdraw' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'command' => Withdraw::class,
            'defaultParams' => [
                'reason' => RefData::PERMIT_APP_WITHDRAW_REASON_USER,
            ],
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_WITHDRAW_CONFIRMATION,
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
}
