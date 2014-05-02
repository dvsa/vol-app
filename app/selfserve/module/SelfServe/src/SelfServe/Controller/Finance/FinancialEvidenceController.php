<?php

/**
 * FinancialEvidence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace SelfServe\Controller\Finance;

use Zend\View\Model\ViewModel;

/**
 * FinancialEvidence Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class FinancialEvidenceController extends AbstractFinanceController
{
    /**
     * Financial Evidence Index
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $applicationId = $this->params()->fromRoute('applicationId');

        $view = $this->getViewModel();
        $view->setTemplate('self-serve/finance/financial-evidence/index');

        // collect completion status
        $completionStatus = $this->makeRestCall('ApplicationCompletion', 'GET', array('application_id' => $applicationId));

        return $this->renderLayout($view, 'financialEvidence',
                                    array('completionStatus' => (($completionStatus['Count']>0)?$completionStatus['Results'][0]:Array()),
                                            'applicationId' => $applicationId));
    }

    public function completeAction()
    {

    }
}
