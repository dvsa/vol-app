<?php

/**
 * WorkshopController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace SelfServe\Controller\VehicleSafety;

use Zend\View\Model\ViewModel;

/**
 * WorkshopController
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class WorkshopController extends AbstractVehicleSafetyController
{
    /**
     * Add workshop
     *
     * @return ViewModel
     */
    public function addAction()
    {
        $applicationId = $this->getApplicationId();

        if ($this->isCancelPressed()) {

            return $this->redirect()->toRoute('selfserve/vehicle-safety/safety-action', array('applicationId' => $applicationId));
        }

        $data = array(

        );

        $form = $this->generateFormWithData('vehicle-safety-workshop', 'processAddWorkshop', $data);

        $view = $this->getViewModel(['form' => $form]);
        $view->setTemplate('self-serve/forms/generic');

        return $this->renderLayoutWithSubSections($view, 'safety');
    }

    public function processAddWorkshop($data)
    {

    }

    public function completeAction()
    {

    }
}
