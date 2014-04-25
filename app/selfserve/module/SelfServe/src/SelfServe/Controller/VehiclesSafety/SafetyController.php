<?php

/**
 * SafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\VehiclesSafety;

use Common\Controller\FormJourneyActionController;
use Zend\View\Model\ViewModel;

/**
 * SafetyController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends FormJourneyActionController
{
    /**
     * Safety form
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $data = array();

        $form = $this->generateFormWithData('vehicle-safety', 'processVehicleSafety', $data);

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/vehicle-safety/safety');

        return $view;
    }

    /**
     * Process the vehicle safety form
     *
     * @param array $data
     */
    private function processVehicleSafety($data)
    {
        print '<pre>';
        print_r($data);
        print '</pre>';
        exit;
    }

    protected function completeAction()
    {

    }
}
