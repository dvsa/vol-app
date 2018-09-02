<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\EcmtSubmitApplication;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;

use Permits\View\Helper\EcmtSection;

class FeeController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $genericTemplate = 'permits/fee';

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_WITH_FEE_LIST,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_CAN_BE_SUBMITTED,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_FEE,
    ];

    public function feeAction()
    {
        if (!empty($this->postParams)) {
            $command = EcmtSubmitApplication::create(['id' => $this->routeParams['id']]);
            $this->handleCommand($command);
            $this->nextStep(EcmtSection::ROUTE_ECMT_SUBMITTED);
        }

        return $this->genericAction();
    }
}
