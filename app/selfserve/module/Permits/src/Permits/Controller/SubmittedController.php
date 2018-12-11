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
        'fee-submitted' => [],
        'fee-waived' => ConditionalDisplayConfig::PERMIT_APP_UNDER_CONSIDERATION
    ];

    protected $templateConfig = [
        'generic' => 'permits/confirmation',
        'fee-submitted' => 'permits/submitted',
        'fee-waived' => 'permits/confirmation'
    ];

    protected $templateVarsConfig = [
        'generic' => [
            'browserTitle' => 'permits.page.confirmation.submitted.browser.title',
            'title' => 'permits.page.confirmation.submitted.title',
            'hasReceipt' => true,
            'extraContent' => [
                'title' => 'permits.page.confirmation.submitted.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-submitted-confirmation'
            ],
            'warning' => 'permits.page.confirmation.submitted.warning',
            'receiptUrl' => ''
        ],
        'fee-waived' => [
            'browserTitle' => 'permits.page.confirmation.fee-waived-submitted.browser.title',
            'title' => 'permits.page.confirmation.fee-waived-submitted.title',
            'extraContent' => [
                'title' => 'permits.page.confirmation.fee-waived-submitted.bullet.list.title',
                'list' => 'en_GB/bullets/markup-ecmt-submitted-confirmation'
            ],
            'warning' => 'permits.page.confirmation.submitted.warning'
        ],
    ];

    public function genericAction()
    {
        $this->data['receiptUrl'] = $this->url()->fromRoute('permits/ecmt-print-receipt', ['id' => $this->params()->fromRoute('id'), 'reference' => $this->params()->fromQuery('receipt_reference')]);

        return parent::genericAction();
    }

    public function feeSubmittedAction()
    {
        $ecmtApplicationId = $this->params()->fromRoute('id');
        $view = parent::genericAction();

        if ($this->params()->fromQuery('receipt_reference') === 'paidWaived') {
            $partialName = 'markup-ecmt-application-fee-submitted-paid-waived';
            $mainName = 'permits.application.fee.submitted.main.paid.waived';
        } else {
            $partialName = 'markup-ecmt-application-fee-submitted';
            $mainName = 'permits.application.fee.submitted.main';
        }

        $view->setVariable('partialName', $partialName);
        $view->setVariable('titleName', 'permits.application.fee.submitted.title');
        $view->setVariable('mainName', $mainName);
        $view->setVariable('receiptUrl', $this->url()->fromRoute('permits/ecmt-print-receipt', ['id' => $ecmtApplicationId, 'reference' => $this->params()->fromQuery('receipt_reference')]));

        return $view;
    }

    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function feeWaivedAction()
    {
        return parent::genericAction();
    }
}
