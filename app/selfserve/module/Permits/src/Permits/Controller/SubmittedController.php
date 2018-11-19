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
        'decline' => [],
        'fee-submitted' => [],
        'fee-waived' => ConditionalDisplayConfig::PERMIT_APP_UNDER_CONSIDERATION
    ];

    protected $templateConfig = [
        'generic' => 'permits/submitted',
        'decline' => 'permits/submitted',
        'fee-submitted' => 'permits/submitted',
        'fee-waived' => 'permits/submitted'
    ];

    public function genericAction()
    {
        $ecmtApplicationId = $this->params()->fromRoute('id');
        $view = parent::genericAction();
        $view->setVariable('partialName', 'markup-ecmt-application-submitted');
        $view->setVariable('titleName', 'permits.application.submitted.title');
        $view->setVariable('mainName', 'permits.application.submitted.main');
        $view->setVariable('receiptUrl', $this->url()->fromRoute('permits/ecmt-print-receipt', ['id' => $ecmtApplicationId, 'reference' => $this->params()->fromQuery('receipt_reference')]));

        return $view;
    }

    public function feeSubmittedAction()
    {
        $ecmtApplicationId = $this->params()->fromRoute('id');
        $view = parent::genericAction();
        $partialName =
            $this->params()->fromQuery('receipt_reference') === 'paidWaived'
            ? 'markup-ecmt-application-fee-submitted-paid-waived'
            : 'markup-ecmt-application-fee-submitted';
        $view->setVariable('partialName', $partialName);
        $view->setVariable('titleName', 'permits.application.fee.submitted.title');
        $mainName =
            $this->params()->fromQuery('receipt_reference') === 'paidWaived'
                ? 'permits.application.fee.submitted.main.paid.waived'
                : 'permits.application.fee.submitted.main';
        $view->setVariable('mainName', $mainName);
        $view->setVariable('receiptUrl', $this->url()->fromRoute('permits/ecmt-print-receipt', ['id' => $ecmtApplicationId, 'reference' => $this->params()->fromQuery('receipt_reference')]));

        return $view;
    }

    public function declineAction()
    {
        $view = parent::genericAction();

        $view->setVariable('partialName', 'markup-ecmt-decline-submitted');
        $view->setVariable('titleName', 'permits.decline.submitted.title');
        $view->setVariable('mainName', 'permits.decline.submitted.main');

        return $view;
    }

    public function feeWaivedAction()
    {
        $view = parent::genericAction();
        $view->setVariables([
            'partialName' => 'markup-ecmt-application-submitted',
            'titleName' => 'permits.application.submitted.title',
            'mainName' => 'permits.application.submitted.main',
            'receiptUrl' => '',
            'visuallyHidden' => 'visually-hidden'
        ]);

        return $view;
    }
}
