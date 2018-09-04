<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtEmissions;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;

class DeclinePermitsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'decline' => ConditionalDisplayConfig::PERMIT_APP_CAN_BE_DECLINE,
        'confirmation' => ConditionalDisplayConfig::PERMIT_APP_IS_DECLINED,
    ];

    protected $formConfig = [
        'decline' => FormConfig::FORM_DECLINE_PERMIT,
    ];


    protected $templateConfig = [
        'decline' => 'permits/decline-application',
        'confirmation' => 'permits/decline-confirmation',
    ];

    protected $postConfig = [
        'decline' => [
            'command' => DeclineEcmtPermitApplication::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_DECLINE_CONFIRMATION
        ],
    ];
}
