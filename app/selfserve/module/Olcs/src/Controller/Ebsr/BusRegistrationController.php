<?php

namespace Olcs\Controller\Ebsr;

use Common\Controller\AbstractActionController;

/**
 * Class BusRegVariationController
 */
class BusRegistrationController extends AbstractActionController
{
    /**
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        /** @var \Common\Service\Table\TableBuilder $tableBuilder */
        $tableBuilder = $this->getServiceLocator()->get('Table');

        $busRegDataService = $this->getBusRegDataService();
        $variationHistory = $busRegDataService->fetchVariationHistory();

        $registrationDetails = $variationHistory[0];

        $variationHistoryTable = $tableBuilder->buildTable(
            'bus-reg-variation-history',
            $variationHistory,
            ['url' => $this->plugin('url')],
            false
        );

        return $this->getView(
            [
                'registrationDetails' => $registrationDetails,
                'variationHistoryTable' => $variationHistoryTable
            ]
        );
    }

    /**
     * @return \Olcs\Service\Data\BusReg
     */
    public function getBusRegDataService()
    {
        /** @var \Common\Service\Data\BusReg $dataService */
        $dataService = $this->getServiceLocator()->get('DataServiceManager')->get('Common\Service\Data\BusReg');
        return $dataService;
    }
}
