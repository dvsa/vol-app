<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\SubmitApplication;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateDeclaration;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\IrhpApplicationSection;

class IrhpApplicationDeclarationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::IRHP_APP
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::IRHP_APP_CAN_MAKE_DECLARATION,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_IRHP_DECLARATION,
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.declaration.browser.title',
            'question' => 'permits.page.declaration.question',
            'bulletList' => [
                'title' => 'permits.page.declaration.bullet.list.title',
                'list' => 'en_GB/bullets/markup-bilateral-declaration'
            ],
            'backUri' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ]
    ];

    protected $postConfig = [
        'default' => [
            'command' => UpdateDeclaration::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_FEE,
            'saveAndReturnStep' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
            'conditional' => [
                'command' => SubmitApplication::class,
                'dataKey' => 'application',
                'params' => 'id',
                'step' => IrhpApplicationSection::ROUTE_SUBMITTED,
                'field' => 'hasOutstandingFees',
                'value' => false
            ]
        ],
    ];
}
