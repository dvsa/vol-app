<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\DeclineEcmtPermits;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;

class DeclineController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'decline' => ConditionalDisplayConfig::PERMIT_APP_CAN_DECLINE,
        'confirmation' => ConditionalDisplayConfig::PERMIT_APP_IS_WITHDRAWN, //declined status to follow?
    ];

    protected $formConfig = [
        'decline' => FormConfig::FORM_DECLINE_PERMIT,
    ];

    protected $templateConfig = [
        'decline' => 'permits/single-question',
        'confirmation' => 'permits/confirmation',
    ];

    protected $templateVarsConfig = [
        'decline' => [
            'browserTitle' => 'permits.page.decline.browser.title',
            'question' => 'permits.page.decline.question',
            'bulletList' => [
                'title' => 'permits.page.decline.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-application-decline'
            ],
            'backUri' => EcmtSection::ROUTE_ECMT_AWAITING_FEE
        ],
        'confirmation' => [
            'browserTitle' => 'permits.page.confirmation.decline.browser.title',
            'title' => 'permits.page.confirmation.decline.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.decline.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-decline-confirmation'
            ]
        ],
    ];

    protected $postConfig = [
        'decline' => [
            'retrieveData' => false,
            'checkConditionalDisplay' => false,
            'command' => DeclineEcmtPermits::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_DECLINE_CONFIRMATION,
        ],
    ];
}
