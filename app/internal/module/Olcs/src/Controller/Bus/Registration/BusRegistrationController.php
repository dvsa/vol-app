<?php

namespace Olcs\Controller\Bus\Registration;

use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Olcs\View\Model\ViewModel;
use Zend\Console\Response;
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

    /**
     * On Dispatch
     *
     * @param MvcEvent $e MvcEvent
     *
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->hlpFlashMsgr = $this->getServiceLocator()->get('Helper\FlashMessenger');

        $this->busRegId = $this->params()->fromRoute('busRegId');

        parent::onDispatch($e);
    }

    /**
     * index action
     *
     * @return \Zend\View\Model\ConsoleModel|ViewModel
     */
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    /**
     *Create Bus Reg
     *
     * @return ViewModel
     */
    public function addAction()
    {
        return $this->process(
            TransferCmd\Bus\CreateBus::create(['licence' => $this->params()->fromRoute('licence')])
        );
    }

    /**
     * Edit Bus Reg
     *
     * @return Response|ViewModel
     */
    public function editAction()
    {
        return $this->redirectToDetails($this->params()->fromRoute('id'));
    }

    /**
     * Create Bus Reg Variation
     *
     * @return Response
     */
    public function createVariationAction()
    {
        return $this->process(
            TransferCmd\Bus\CreateVariation::create(['id' => $this->busRegId])
        );
    }

    /**
     * Create Bus Reg Cancellation
     *
     * @return Response
     */
    public function createCancellationAction()
    {
        return $this->process(
            TransferCmd\Bus\CreateCancellation::create(['id' => $this->busRegId])
        );
    }

    /**
     * redirect to details
     *
     * @param string $id $id
     *
     * @return \Zend\Http\Response
     */
    private function redirectToDetails($id)
    {
        return $this->redirect()->toRouteAjax('licence/bus-details/service', ['busRegId' => $id], [], true);
    }

    /**
     * Process command
     *
     * @param TransferCmd\CommandInterface $command commmand
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

    /**
     * print letter action
     *
     * @return \Zend\Http\Response
     */
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
