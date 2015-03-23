<?php

/**
 * Bus Registration Controller
 */
namespace Olcs\Controller\Bus\Registration;

use Olcs\Controller\Bus\BusController;
use Common\Service\BusRegistration as BusRegistrationService;

/**
 * Bus Registration Controller
 */
class BusRegistrationController extends BusController
{
    /**
     * @var BusRegistrationService $busRegistrationService
     */
    protected $busRegistrationService;

    /**
     * Get Bus Registration Service
     */
    protected function getBusRegistrationService()
    {
        if (is_null($this->busRegistrationService)) {
            $this->busRegistrationService = new BusRegistrationService();
        }
        return $this->busRegistrationService;
    }

    /**
     * Set Bus Registration Service
     *
     * @param BusRegistrationService $busRegistrationService
     */
    public function setBusRegistrationService($busRegistrationService)
    {
        $this->busRegistrationService = $busRegistrationService;
    }

    /**
     * Create Bus Reg
     */
    public function addAction()
    {
        // get licence details
        $licenceId = $this->getFromRoute('licence');
        $licence = $this->getServiceLocator()->get('Entity\Licence')->getById($licenceId);

        // get default Bus Reg details
        $data = $this->getBusRegistrationService()->createNew($licence);

        // get the most recent Route No for the licence
        $busRegEntityService = $this->getServiceLocator()->get('Entity\BusReg');
        $busRegWithMostRecentRouteNo = $busRegEntityService->findMostRecentRouteNoByLicence($licence['id']);

        // increment Route No
        $newRouteNo = (int)$busRegWithMostRecentRouteNo['routeNo'] + 1;

        // set Route No and Reg No
        $data['routeNo'] = $newRouteNo;
        $data['regNo'] = $licence['licNo'].'/'.$newRouteNo;

        // save the data
        $busReg = $busRegEntityService->save($data);

        return $this->redirect()->toRouteAjax(
            'licence/bus-details/service',
            ['busRegId' => $busReg['id']],
            [],
            true
        );
    }

    /**
     * Edit Bus Reg
     */
    public function editAction()
    {
        return $this->redirect()->toRouteAjax(
            'licence/bus-details/service',
            ['busRegId' => $this->getFromRoute('id')],
            [],
            true
        );
    }

    /**
     * Create Bus Reg Variation
     */
    public function createVariationAction()
    {
        return $this->createRecord('createVariation');
    }

    /**
     * Create Bus Reg Cancellation
     */
    public function createCancellationAction()
    {
        return $this->createRecord('createCancellation');
    }

    /**
     * Create Record
     * Creates Bus Reg record based on the existing one with required modifications
     *
     * @param array $action
     * @return Redirect
     */
    private function createRecord($action)
    {
        $busRegEntityService = $this->getServiceLocator()->get('Entity\BusReg');

        // get Bus Reg details
        $busRegId = $this->getFromRoute('busRegId');
        $busReg = $busRegEntityService->getDataForVariation($busRegId);

        $mostRecent = $busRegEntityService->findMostRecentByIdentifier($busReg['regNo']);

        // get default Bus Reg Variation details
        $data = $this->getBusRegistrationService()->$action($busReg, $mostRecent);

        // save the data
        $busRegVariation = $busRegEntityService->save($data);

        return $this->redirect()->toRouteAjax(
            'licence/bus-details/service',
            ['busRegId' => $busRegVariation['id']],
            [],
            true
        );
    }
}
