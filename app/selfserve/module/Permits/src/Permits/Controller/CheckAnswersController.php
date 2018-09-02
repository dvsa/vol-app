<?php
namespace Permits\Controller;

use Common\Controller\Interfaces\ToggleAwareInterface;
use Dvsa\Olcs\Transfer\Command\Permits\UpdateEcmtCheckAnswers;
use Olcs\Controller\AbstractSelfserveController;
use Permits\Controller\Config\DataSource\DataSourceConfig;
use Permits\Controller\Config\ConditionalDisplay\ConditionalDisplayConfig;
use Permits\Controller\Config\FeatureToggle\FeatureToggleConfig;
use Permits\Controller\Config\Form\FormConfig;

use Permits\View\Helper\EcmtSection;

class CheckAnswersController extends AbstractSelfserveController implements ToggleAwareInterface
{
    protected $genericTemplate = 'permits/check-answers';

    protected $toggleConfig = [
        'default' => FeatureToggleConfig::SELFSERVE_ECMT_ENABLED,
    ];

    protected $dataSourceConfig = [
        'default' => DataSourceConfig::PERMIT_APP_WITH_FEE_LIST,
    ];

    protected $conditionalDisplayConfig = [
        'default' => ConditionalDisplayConfig::PERMIT_APP_CAN_CHECK_ANSWERS,
    ];

    protected $formConfig = [
        'default' => FormConfig::FORM_CHECK_ANSWERS,
    ];

    public function checkAnswersAction()
    {
        if (!empty($this->postParams)) {
            $command = UpdateEcmtCheckAnswers::create(['id' => $this->routeParams['id']]);
            $this->handleCommand($command);
            $this->nextStep(EcmtSection::ROUTE_ECMT_DECLARATION);
        }

        return $this->genericAction();
    }
}
