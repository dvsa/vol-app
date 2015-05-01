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
    use Lva\Traits\ExternalControllerTrait;

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

        return $view;
    }

    /**
     * @param array $fees
     * @return array
     */
    protected function formatTableData($fees)
    {
        $tableData = [];
        foreach ($fees['Results'] as $fee) {
            $fee['licNo'] = $fee['licence']['licNo'];
            unset($fee['licence']);
            $tableData[] = $fee;
        }
        return $tableData;
    }
}
