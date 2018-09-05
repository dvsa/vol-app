<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Dvsa\Olcs\Transfer\Command\Permits\WithdrawEcmtPermitApplication;

use Permits\View\Helper\EcmtSection;

class DeclineApplicationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_UNDER_CONSIDERATION,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_DECLINE_PERMIT,
    ];


    protected $templateConfig = [
        'generic' => 'permits/decline-application',
    ];

    protected $postConfig = [
        'decline' => [
            'command' => WithdrawEcmtPermitApplication::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_DECLARATION
        ],
    ];

}
