<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateDeclaration;
use Dvsa\Olcs\Transfer\Command\Permits\EcmtSubmitApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;

class DeclarationController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_CAN_MAKE_DECLARATION,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_DECLARATION,
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.declaration.browser.title',
            'question' => 'permits.page.declaration.question',
            'bulletList' => [
                'title' => 'permits.page.declaration.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-declaration'
            ]
        ]
    ];

    protected $postConfig = [
        'default' => [
            'command' => UpdateDeclaration::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_ECMT_FEE,
            'conditional' => [
                'command' => EcmtSubmitApplication::class,
                'dataKey' => 'application',
                'params' => 'id',
                'step' => EcmtSection::ROUTE_ECMT_FEE_WAIVED_SUBMITTED,
                'field' => 'hasOutstandingFees',
                'value' => 0
            ]
        ],
    ];
}
