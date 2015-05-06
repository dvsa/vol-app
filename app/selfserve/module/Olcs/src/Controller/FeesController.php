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
use Common\Exception\ResourceNotFoundException;

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

        if (!empty($fees)) {
            $fees = $fees['Results'];
        }

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
        $fees = $this->getFeesFromParams();

        if (empty($fees)) {
            throw new ResourceNotFoundException('Fee not found');
        }

        // if ($this->getRequest()->isPost()) {
        //     var_dump($this->getRequest()->getPost(), $fees); exit;
        // }

        $form = $this->getForm();

        if (count($fees) > 1) {
            $table = $this->getServiceLocator()->get('Table')
                ->buildTable('pay-fees', $this->formatTableData($fees));
            $view = new ViewModel(['table' => $table, 'form' => $form]);
            $view->setTemplate('pay-fees');
        } else {
            $fee = array_shift($fees);
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
            foreach ($fees as $fee) {
                $fee['licNo'] = $fee['licence']['licNo'];
                unset($fee['licence']);
                $tableData[] = $fee;
            }
        }

        return $tableData;
    }

    /**
     * Get fees by ID(s) from params, note these *must* be a subset of the
     * outstanding fees for the current organisation - any invalid IDs are
     * ignored
     */
    protected function getFeesFromParams()
    {
        $fees = [];

        $organisationId = $this->getCurrentOrganisationId();
        $outstandingFees = $this->getServiceLocator()->get('Entity\Fee')
            ->getOutstandingFeesForOrganisation($organisationId);

        $ids = explode(',', $this->params('fee'));
        if (!empty($outstandingFees)) {
            foreach ($outstandingFees['Results'] as $fee) {
                if (in_array($fee['id'], $ids)) {
                    $fees[] = $fee;
                }
            }
        }

        return $fees;
    }

    protected function getForm()
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('FeePayment');

        return $form;
    }
}
