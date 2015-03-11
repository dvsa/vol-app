<?php

/**
 * Abstract Transport Manager Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\TransportManagerController;
use Common\Controller\Traits\GenericUpload;
use Zend\View\Model\ViewModel;

/**
 * Abstract Transport Manager Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractTransportManagerDetailsController extends TransportManagerController
{
    use GenericUpload;

    /**
     * Redirect to index
     *
     * @return Redirect
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
        $translator = $this->getServiceLocator()->get('translator');
        $id = $this->getFromRoute('id');
        if (!$id) {
            // multiple delete
            $id = $this->params()->fromQuery('id');
        }
        $response = $this->confirm(
            $translator->translate('internal.transport-manager.previous-history.delete-question')
        );

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }
        if (!$this->isButtonPressed('cancel')) {
            $this->getServiceLocator()->get($serviceName)->deleteListByIds(['id' => !is_array($id) ? [$id] : $id]);
            $this->addSuccessMessage('internal.transport-manager.deleted-message');
        }
        return $this->redirectToIndex();
    }
}
