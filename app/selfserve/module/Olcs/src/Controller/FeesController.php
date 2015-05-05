<?php

/**
 * Fees Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace Olcs\Controller;

use Olcs\View\Model\Fees;
use Common\Controller\Lva\AbstractController;
use Zend\View\Model\ViewModel;

/**
 * Fees Controller
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class FeesController extends AbstractController
{
    use Lva\Traits\ExternalControllerTrait,
        Lva\Traits\DashboardNavigationTrait;

    /**
     * Fees index action
     */
    public function indexAction()
    {
        $organisationId = $this->getCurrentOrganisationId();
        $fees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForOrganisation($organisationId);

        $table = $this->getServiceLocator()->get('Table')
            ->buildTable('fees', $this->formatTableData($fees));

        $view = new ViewModel(['table' => $table]);
        $view->setTemplate('fees');

        // populate the navigation tabs with correct counts
        $count = isset($fees['Count']) ? $fees['Count'] : null;
        $this->populateTabCounts($count);

        return $view;
    }

    /**
     * Pay Fees action
     */
    public function payFeesAction()
    {
        $feeData = $this->getFeesFromParams();

        $form = $this->getForm();

        if (count($feeData) > 1) {
            $table = $this->getServiceLocator()->get('Table')
                ->buildTable('pay-fees', $feeData);
            $view = new ViewModel(['table' => $table, 'form' => $form]);
            $view->setTemplate('pay-fees');
        } else {
            $fee = array_shift($feeData);
            $view = new ViewModel(['fee' => $fee, 'form' => $form]);
            $view->setTemplate('pay-fee');
        }

        return $view;
    }

    /**
     * @param array $fees
     * @return array
     */
    protected function formatTableData($fees)
    {
        $tableData = [];

        if (!empty($fees)) {
            foreach ($fees['Results'] as $fee) {
                $fee['licNo'] = $fee['licence']['licNo'];
                unset($fee['licence']);
                $tableData[] = $fee;
            }
        }

        return $tableData;
    }

    /**
     * @todo
     */
    protected function getFeesFromParams()
    {
        $organisationId = $this->getCurrentOrganisationId();
        $fees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForOrganisation($organisationId);
        return $this->formatTableData($fees);
    }

    protected function getForm()
    {
        return null;
    }
}
