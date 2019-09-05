<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtRoadworthiness;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;
use Permits\View\Helper\EcmtSection;

class RoadworthinessController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_NOT_SUBMITTED,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_ROADWORTHINESS,
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.roadworthiness.browser.title',
            'question' => 'permits.page.roadworthiness.question',
            'additionalGuidance' => [
                'disableHtmlEscape' => true,
                'value' => 'permits.page.roadworthiness.guidance',
            ],
        ]
    ];

    protected $postConfig = [
        'default' => [
            'retrieveData' => true,
            'checkConditionalDisplay' => true,
            'command' => UpdateEcmtRoadworthiness::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_COUNTRIES,
            'saveAndReturnStep' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];
}
