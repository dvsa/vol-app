<?php

namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\Withdraw;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\View\Helper\IrhpApplicationSection;

class IrhpDeclineController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP,
    ];

    protected $conditionalDisplayConfig = [
        'withdraw' => ConditionalDisplayConfig::IRHP_APP_CAN_BE_DECLINED,
        'confirmation' => ConditionalDisplayConfig::IRHP_APP_IS_DECLINED,
    ];

    protected $formConfig = [
        'withdraw' => FormConfig::FORM_DECLINE_PERMIT,
    ];

    protected $templateConfig = [
        'withdraw' => 'permits/single-question',
        'confirmation' => 'permits/confirmation',
    ];

    protected $templateVarsConfig = [
        'withdraw' => [
            'browserTitle' => 'permits.page.decline.browser.title',
            'question' => 'permits.page.decline.question',
            'bulletList' => [
                'title' => 'permits.page.decline.bullet.list.title',
                'list' => 'markup-ecmt-application-decline',
            ],
            'backUri' => IrhpApplicationSection::ROUTE_AWAITING_FEE,
        ],
        'confirmation' => [
            'browserTitle' => 'permits.page.confirmation.decline.browser.title',
            'title' => 'permits.page.confirmation.decline.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title',
                'list' => 'markup-ecmt-decline-confirmation',
            ],
        ],
    ];

    protected $postConfig = [
        'withdraw' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'command' => Withdraw::class,
            'defaultParams' => [
                'reason' => RefData::PERMIT_APP_WITHDRAW_REASON_DECLINED,
            ],
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_DECLINE_CONFIRMATION,
        ],
    ];
}
