<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;

class SubmittedController extends AbstractSelfserveController implements ToggleAwareInterface
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

    protected $templateConfig = [
        'generic' => 'permits/submitted',
        'fee-submitted' => 'permits/submitted'
    ];

    public function genericAction()
    {
        $view = parent::genericAction();
        $view->setVariable('partialName', 'markup-ecmt-application-submitted');
        $view->setVariable('titleName', 'permits.application.submitted.title');

        return $view;
    }

    public function feeSubmittedAction()
    {
        $view = parent::genericAction();
        $view->setVariable('partialName', 'markup-ecmt-application-fee-submitted');
        $view->setVariable('titleName', 'permits.application.fee.submitted.title');

        return $view;
    }
}
