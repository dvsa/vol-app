<?php

/**
 * Abstract Transport Manager Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\TransportManagerController;
use Common\Controller\Traits\GenericUpload;

/**
 * Abstract Transport Manager Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractTransportManagerDetailsController extends TransportManagerController
{
    use GenericUpload;

    /**
     * Delete file
     *
     * @param int $id
     * @return Redirect
     */
    public function deleteTmFile($id)
    {
        $documentService = $this->getServiceLocator()->get('Entity\Document');

        $identifier = $documentService->getIdentifier($id);

        if (!empty($identifier)) {
            $this->getServiceLocator()->get('FileUploader')->getUploader()->remove($identifier);
        }

        $documentService->delete($id);

        $tm = $this->getFromRoute('transportManager');
        $action = $this->getFromRoute('action');
        $title = $this->getFromRoute('title');

        $routeParams = ['transportManager' => $tm, 'action' => $action];
        if ($title) {
            $routeParams['title'] = $title;
        }
        return $this->redirect()->toRouteAjax(null, $routeParams, [], true);
    }

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
}
