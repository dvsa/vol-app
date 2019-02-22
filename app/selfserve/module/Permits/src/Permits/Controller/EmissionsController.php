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

class EmissionsController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_ECMT_EMISSIONS,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_NOT_SUBMITTED,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_EMISSIONS,
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.euro6.emissions.browser.title',
            'question' => 'permits.page.euro6.emissions.question',
            'additionalGuidance' => [
                'permits.page.euro6.emissions.guidance.line.1',
                'permits.page.euro6.emissions.guidance.line.2',
            ],
        ]
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => false,
            'command' => UpdateEcmtEmissions::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_CABOTAGE,
        ],
    ];
}
