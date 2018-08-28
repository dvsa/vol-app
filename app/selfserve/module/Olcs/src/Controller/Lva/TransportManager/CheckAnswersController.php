<?php

namespace OLCS\Controller\Lva\TransportManager;

use Common\Controller\Lva\AbstractTransportManagersController;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetDetails;
use Olcs\View\Model\ViewModel;

class CheckAnswersController extends AbstractTransportManagersController
{


    public function indexAction()
    {
        $transportManagerApplicationId = $this->params("application");
        /**
         * @var TransportManagerApplication
         */
        $transportManagerApplication = $this->handleQuery(
            GetDetails::create(['id' => $transportManagerApplicationId])
        )->getResult();

        var_dump($transportManagerApplication);
        exit();
    }

    public function reviewAction()
    {
        $transportManagerApplicationId = $this->params("id");
        /**
         * @var TransportManagerApplication
         */
        $transportManagerApplication = $this->handleQuery(
            GetDetails::create(['id' => $transportManagerApplicationId])
        )->getResult();

        $viewModel = new ViewModel();
    }

    public function returnAction()
    {
        return $this->redirect()->toRoute(
            'dashboard',
            []
        );
    }

    public function changeAction()
    {
        $section = $this->params("section");
        $transportManagerApplicationId = $this->params("id");

        $routeParams = ['child_id' => $transportManagerApplicationId];
        $route = ['name' =>'', 'params'=>$routeParams];
    }

}