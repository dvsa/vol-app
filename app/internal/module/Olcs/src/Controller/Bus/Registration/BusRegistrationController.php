<?php

namespace Olcs\Controller\Bus\Registration;

use Common\Service\Helper\FlashMessengerHelperService;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Laminas\Http\Response;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Mvc\MvcEvent;
use Olcs\Controller\Interfaces\BusRegControllerInterface;
use Olcs\View\Model\ViewModel;

/**
 * Bus Registration Controller
 *
 * @method \Common\Service\Cqrs\Response handleCommand(\Dvsa\Olcs\Transfer\Command\CommandInterface $query)
 * @method \Common\Controller\Plugin\Redirect redirect()
 */
class BusRegistrationController extends AbstractActionController implements BusRegControllerInterface
{
    /**
     * @var int
     */
    private $busRegId;

    protected FlashMessengerHelperService $flashMessenger;

    public function __construct(protected FlashMessengerHelperService $flashMessengerHelperService)
    {
    }

    /**
     * On Dispatch
     *
     * @param MvcEvent $e MvcEvent
     *
     * @return void
     */
    public function onDispatch(MvcEvent $e)
    {
        $this->busRegId = $this->params()->fromRoute('busRegId');
        parent::onDispatch($e);
    }

    /**
     * index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        return $this->notFoundAction();
    }

    /**
     * Create Bus Reg
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
     * @return \Laminas\Http\Response
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
     * @return \Laminas\Http\Response
     */
    private function process($command)
    {
        $response = $this->handleCommand($command);

        if ($response->isOk()) {
            $this->flashMessengerHelperService->addSuccessMessage('Created record');
        } else {
            $this->flashMessengerHelperService->addUnknownError();
        }

        return $this->redirectToDetails($response->getResult()['id']['bus']);
    }

    /**
     * print letter action
     *
     * @return \Laminas\Http\Response
     */
    public function printLetterAction()
    {
        $response = $this->handleCommand(
            TransferCmd\Bus\PrintLetter::create(
                ['id' => $this->busRegId]
            )
        );

        if ($response->isOk()) {
            $this->flashMessengerHelperService->addSuccessMessage('Bus registration letter created');
        } else {
            $this->flashMessengerHelperService->addErrorMessage('Bus registration letter not created');
        }

        return $this->redirect()->toRouteAjax('licence/bus-docs', ['busRegId' => $this->busRegId], [], true);
    }
}
