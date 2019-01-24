<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\CancelApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\IrhpApplicationSection;

class CancelIrhpApplicationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

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
            'question' => 'permits.page.cancel.question',
            'bulletList' => [
                'title' => 'permits.page.cancel.bullet.list.title',
                'list' => 'en_GB/bullets/markup-irhp-application-cancel'
            ],
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW
        ],
        'confirmation' => [
            'browserTitle' => 'permits.page.confirmation.cancel.browser.title',
            'title' => 'permits.page.confirmation.cancel.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.bullet.list.title',
                'list' => 'en_GB/bullets/markup-irhp-cancel-confirmation'
            ]
        ],
    ];

    protected $postConfig = [
        'cancel' => [
            'retrieveData' => false,
            'checkConditionalDisplay' => false,
            'command' => CancelApplication::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_CANCEL_CONFIRMATION,
        ],
    ];
}
