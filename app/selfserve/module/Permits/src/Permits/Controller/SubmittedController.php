<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class SubmittedController extends AbstractSelfserveController implements ToggleAwareInterface
{
    private $partialConfig = [
        'ecmt-submitted' => 'markup-ecmt-application-submitted',
        'ecmt-fee-submitted' => ''
    ];

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_UNDER_CONSIDERATION,
    ];

    protected $templateConfig = [
        'generic' => 'permits/submitted'
    ];

    public function genericView()
    {
        $view = parent::genericView();

        foreach ($partialConfig as $actionName => $partialName) {
            if ($actionName === $this->action) {
                $view->setVariable('partialName', $partialName);
            }
        }

        return $view;
    }
}
