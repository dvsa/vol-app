<?php

namespace Olcs\Controller\Bus\Registration;

use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Zend\Mvc\Controller\AbstractActionController;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Zend\Mvc\MvcEvent;

/**
 * Bus Registration Controller
 *
 * @method \Common\Service\Cqrs\Response handleCommand(\Dvsa\Olcs\Transfer\Command\CommandInterface $query)
 * @method \Common\Controller\Plugin\Redirect redirect()
 */
class BusRegistrationController extends AbstractActionController implements BusRegControllerInterface
{
    /** @var  FlashMessengerHelperService */
    private $hlpFlashMsgr;

    /** @var  int */
    private $busRegId;

    public function onDispatch(MvcEvent $e)
    {
        $this->hlpFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');

        $this->busRegId = $this->params()->fromRoute('busRegId');

        parent::onDispatch($e);
    }

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
            TransferCmd\Bus\CreateBus::create(['licence' => $this->params()->fromRoute('licence')])
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
            TransferCmd\Bus\CreateVariation::create(['id' => $this->busRegId])
        );
    }

    /**
     * Create Bus Reg Cancellation
     */
    public function createCancellationAction()
    {
        return $this->process(
            TransferCmd\Bus\CreateCancellation::create(['id' => $this->busRegId])
        );
    }

    private function redirectToDetails($id)
    {
        return $this->redirect()->toRouteAjax('licence/bus-details/service', ['busRegId' => $id], [], true);
    }

    /**
     * Process command
     *
     * @param TransferCmd\CommandInterface $command
     *
     * @return \Zend\Http\Response
     */
    private function process($command)
    {
        $response = $this->handleCommand($command);

        if ($response->isOk()) {
            $this->hlpFlashMsgr->addSuccessMessage('Created record');
        } else {
            $this->hlpFlashMsgr->addUnknownError();
        }

        return $this->redirectToDetails($response->getResult()['id']['bus']);
    }

    public function printLetterAction()
    {
        $response = $this->handleCommand(
            TransferCmd\Bus\PrintLetter::create(
                ['id' => $this->busRegId]
            )
        );

        if ($response->isOk()) {
            $this->hlpFlashMsgr->addSuccessMessage('Bus registration letter created');
        } else {
            $this->hlpFlashMsgr->addErrorMessage('Bus registration letter not created');
        }

        return $this->redirect()->toRouteAjax('licence/bus-docs', ['busRegId' => $this->busRegId], [], true);
    }
}
