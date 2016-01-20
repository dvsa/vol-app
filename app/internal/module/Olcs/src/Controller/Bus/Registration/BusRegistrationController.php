<?php

/**
 * Bus Registration Controller
 */
namespace Olcs\Controller\Bus\Registration;

use Dvsa\Olcs\Transfer\Command\Bus\CreateBus as CreateBusDto;
use Dvsa\Olcs\Transfer\Command\Bus\CreateVariation as CreateVariationDto;
use Dvsa\Olcs\Transfer\Command\Bus\CreateCancellation as CreateCancellationDto;
use Zend\Mvc\Controller\AbstractActionController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;

/**
 * Bus Registration Controller
 */
class BusRegistrationController extends AbstractActionController implements BusRegControllerInterface
{
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Create Bus Reg
     */
    public function addAction()
    {
        return $this->process(
            CreateBusDto::class,
            ['licence' => $this->params()->fromRoute('licence')]
        );
    }

    /**
     * Edit Bus Reg
     */
    public function editAction()
    {
        return $this->redirectToDetails($this->params()->fromRoute('id'));
    }

    /**
     * Create Bus Reg Variation
     */
    public function createVariationAction()
    {
        return $this->process(
            CreateVariationDto::class,
            ['id' => $this->params()->fromRoute('busRegId')]
        );
    }

    /**
     * Create Bus Reg Cancellation
     */
    public function createCancellationAction()
    {
        return $this->process(
            CreateCancellationDto::class,
            ['id' => $this->params()->fromRoute('busRegId')]
        );
    }

    private function redirectToDetails($id)
    {
        return $this->redirect()->toRouteAjax(
            'licence/bus-details/service',
            ['busRegId' => $id],
            [],
            true
        );
    }

    private function process($command, $data)
    {
        $response = $this->handleCommand($command::create($data));

        if ($response->isServerError() || $response->isClientError()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addErrorMessage('unknown-error');
        }

        if ($response->isOk()) {
            $this->getServiceLocator()->get('Helper\FlashMessenger')->addSuccessMessage('Created record');
            return $this->redirectToDetails($response->getResult()['id']['bus']);
        }
    }
}
