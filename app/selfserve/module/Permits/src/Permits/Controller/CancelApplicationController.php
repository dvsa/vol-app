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
        'cancel' => 'permits/cancel-application',
        'confirmation' => 'permits/cancel-confirmation',
    ];

    protected $postConfig = [
        'cancel' => [
            'command' => CancelEcmtPermitApplication::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_CANCEL_CONFIRMATION
        ],
    ];
}
