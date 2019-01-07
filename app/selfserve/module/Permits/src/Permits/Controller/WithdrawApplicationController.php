<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Common\RefData;
use Dvsa\Olcs\Transfer\Command\Permits\WithdrawEcmtPermitApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;

class WithdrawApplicationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'withdraw' => ConditionalDisplayConfig::PERMIT_APP_CAN_BE_WITHDRAWN,
        'confirmation' => ConditionalDisplayConfig::PERMIT_APP_IS_WITHDRAWN,
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
            'question' => 'permits.page.withdraw.question',
            'bulletList' => [
                'title' => 'permits.page.withdraw.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-application-withdraw'
            ],
            'backUri' => EcmtSection::ROUTE_ECMT_UNDER_CONSIDERATION
        ],
        'confirmation' => [
            'browserTitle' => 'permits.page.confirmation.withdraw.browser.title',
            'title' => 'permits.page.confirmation.withdraw.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-withdraw-confirmation'
            ]
        ],
    ];

    protected $postConfig = [
        'withdraw' => [
            'retrieveData' => false,
            'checkConditionalDisplay' => false,
            'command' => WithdrawEcmtPermitApplication::class,
            'defaultParams' => [
                'reason' => RefData::PERMIT_APP_WITHDRAW_REASON_USER,
            ],
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_WITHDRAW_CONFIRMATION
        ],
    ];
}
