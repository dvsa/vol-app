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
        $view = $this->getViewModel();
        $view->setTemplate('self-serve/finance/financial-evidence/index');

        return $this->renderLayout($view);
    }

    public function completeAction()
    {

    }
}
