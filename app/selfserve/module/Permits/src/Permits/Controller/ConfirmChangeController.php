<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateLicence;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\IrhpApplicationSection;

class ConfirmChangeController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_PERMITS_ENABLED,
    ];

    protected $dataSourceConfig = [
        'question' => DataSourceConfig::PERMIT_APP_CHANGE_LICENCE,
    ];

    protected $conditionalDisplayConfig = [
        'question' => ConditionalDisplayConfig::PERMIT_APP_CONFIRM_CHANGE_LICENCE,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_CONFIRM_CHANGE_LICENCE,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'question' => [
            'browserTitle' => 'permits.page.change-licence.browser.title',
            'question' => 'permits.page.change-licence.question',
            'bulletList' => [
                'title' => 'permits.page.change-licence.bullet.list.title',
                'list' => 'markup-ecmt-licence-change'
            ],
            'backUri' => IrhpApplicationSection::ROUTE_LICENCE
        ]
    ];

    protected $postConfig = [
        'question' => [
            'command' => UpdateLicence::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];
}
