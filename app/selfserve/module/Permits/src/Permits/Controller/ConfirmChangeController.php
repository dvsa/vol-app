<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\IrhpApplication\UpdateLicence;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCabotage;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtLicence;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;
use Permits\Controller\Config\Params\ParamsConfig;

use Permits\View\Helper\EcmtSection;
use Permits\View\Helper\IrhpApplicationSection;

class ConfirmChangeController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'ecmt' => DataSourceConfig::PERMIT_APP_ECMT_LICENCE,
        'question' => DataSourceConfig::PERMIT_APP_LICENCE,
    ];

    protected $conditionalDisplayConfig = [
        'ecmt' => ConditionalDisplayConfig::PERMIT_APP_CONFIRM_CHANGE_LICENCE_ECMT,
        'question' => ConditionalDisplayConfig::PERMIT_APP_CONFIRM_CHANGE_LICENCE,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_CONFIRM_CHANGE_LICENCE,
    ];

    protected $templateConfig = [
        'default' => 'permits/single-question'
    ];

    protected $templateVarsConfig = [
        'ecmt' => [
            'browserTitle' => 'permits.page.change-licence.browser.title',
            'question' => 'permits.page.change-licence.question',
            'bulletList' => [
                'title' => 'permits.page.change-licence.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-licence-change'
            ]
        ],
        'question' => [
            'browserTitle' => 'permits.page.change-licence.browser.title',
            'question' => 'permits.page.change-licence.question',
            'bulletList' => [
                'title' => 'permits.page.change-licence.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-licence-change'
            ]
        ]
    ];

    protected $postConfig = [
        'ecmt' => [
            'command' => UpdateEcmtLicence::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => EcmtSection::ROUTE_APPLICATION_OVERVIEW,
        ],
        'question' => [
            'command' => UpdateLicence::class,
            'params' => ParamsConfig::ID_FROM_ROUTE,
            'step' => IrhpApplicationSection::ROUTE_APPLICATION_OVERVIEW,
        ],
    ];

    /**
     * @return \Zend\Http\Response|\Zend\View\Model\ViewModel
     */
    public function ecmtAction()
    {
        return $this->questionAction();
    }
}
