<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Withdraw;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\DataSource\IrhpApplication as IrhpAppDataSource;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpWithdrawController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

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
                'list' => 'markup-ecmt-application-withdraw',
            ],
            'backUri' => IrhpApplicationSection::ROUTE_UNDER_CONSIDERATION,
        ],
        'confirmation' => [
            'browserTitle' => 'permits.page.confirmation.withdraw.browser.title',
            'prependTitleDataKey' => IrhpAppDataSource::DATA_KEY,
            'title' => 'permits.page.confirmation.withdraw.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title',
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
}
