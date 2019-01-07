<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\CancelEcmtPermitApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;

class CancelApplicationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'cancel' => ConditionalDisplayConfig::PERMIT_APP_CAN_BE_CANCELLED,
        'confirmation' => ConditionalDisplayConfig::PERMIT_APP_IS_CANCELLED,
    ];

    protected $formConfig = [
        'cancel' => FormConfig::FORM_CANCEL_PERMIT_APP,
    ];

    protected $templateConfig = [
        'cancel' => 'permits/single-question',
        'confirmation' => 'permits/confirmation',
    ];

    protected $templateVarsConfig = [
        'cancel' => [
            'browserTitle' => 'permits.page.cancel.browser.title',
            'question' => 'permits.page.cancel.question',
            'bulletList' => [
                'title' => 'permits.page.cancel.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-application-cancel'
            ]
        ],
        'confirmation' => [
            'browserTitle' => 'permits.page.confirmation.cancel.browser.title',
            'title' => 'permits.page.confirmation.cancel.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-cancel-confirmation'
            ]
        ],
    ];

    protected $postConfig = [
        'cancel' => [
            'retrieveData' => false,
            'checkConditionalDisplay' => false,
            'command' => CancelEcmtPermitApplication::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_CANCEL_CONFIRMATION
        ],
    ];
}
