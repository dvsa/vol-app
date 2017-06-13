<?php

namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\TransportManagerController;
use Common\Controller\Traits\GenericUpload;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;
use Zend\View\Model\ViewModel;

/**
 * Abstract Transport Manager Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractTransportManagerDetailsController extends TransportManagerController implements FactoryInterface
{
    use GenericUpload;

    /** @var  \Common\Service\Helper\FormHelperService */
    protected $formHelper;
    /** @var  \Common\Service\Helper\TransportManagerHelperService */
    protected $transportManagerHelper;

    /**
     * Create service
     *
     * @param \Zend\Mvc\Controller\ControllerManager $serviceLocator Service Manager
     *
     * @return $this
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $sm = $serviceLocator->getServiceLocator();

        $this->formHelper = $sm->get('Helper\Form');
        $this->transportManagerHelper = $sm->get('Helper\TransportManager');

        return $this;
    }

    /**
     * Redirect to index
     *
     * @return \Zend\Http\Response
     */
    public function redirectToIndex()
    {
        $tm = $this->getFromRoute('transportManager');
        $routeParams = ['transportManager' => $tm];
        return $this->redirect()->toRouteAjax(null, $routeParams);
    }

    /**
     * Delete record or multiple records
     *
     * @param string $serviceName
     * @return mixed
     */
    protected function deleteRecords($serviceName)
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }
        $translator = $this->getServiceLocator()->get('translator');
        $id = $this->getFromRoute('id');
        if (!$id) {
            // multiple delete
            $id = $this->params()->fromQuery('id');
        }

        if (is_string($id) && strstr($id, ',')) {
            $id = explode(',', $id);
        }

        $response = $this->confirm(
            $translator->translate('transport-manager.previous-history.delete-question')
        );

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $this->getServiceLocator()->get($serviceName)->deleteListByIds(['id' => !is_array($id) ? [$id] : $id]);
        $this->addSuccessMessage('transport-manager.deleted-message');

        return $this->redirectToIndex();
    }

    /**
     * Delete record or multiple records
     *
     * @param string $command DTO class name
     * @return mixed
     */
    protected function deleteRecordsCommand($command)
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }
        $translator = $this->getServiceLocator()->get('translator');
        $id = $this->getFromRoute('id');
        if (!$id) {
            // multiple delete
            $id = $this->params()->fromQuery('id');
        }

        if (is_string($id) && strstr($id, ',')) {
            $id = explode(',', $id);
        }

        $response = $this->confirm(
            $translator->translate('transport-manager.previous-history.delete-question')
        );

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }

        $ids = !is_array($id) ? [$id] : $id;
        $commandResponse = $this->handleCommand($command::create(['ids' => $ids]));
        if (!$commandResponse->isOk()) {
            throw new \RuntimeException('Error deleting '. $command);
        }

        $this->addSuccessMessage('generic.deleted.success');

        return $this->redirectToIndex();
    }
}
