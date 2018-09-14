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
        'generic' => ConditionalDisplayConfig::PERMIT_APP_CAN_DECLINE,
        'confirmation' => ConditionalDisplayConfig::PERMIT_APP_IS_WITHDRAWN, //declined status to follow?
    ];

    protected $formConfig = [
        'generic' => FormConfig::FORM_DECLINE_PERMIT,
    ];

    protected $templateConfig = [
        'generic' => 'permits/decline-application',
        'confirmation' => 'permits/submitted',
    ];

    protected $postConfig = [
        'generic' => [
            'command' => DeclineEcmtPermits::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_DECLINE_CONFIRMATION,
        ],
    ];
}
