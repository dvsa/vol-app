<?php

/**
 * Abstract Transport Manager Details Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\TransportManagerController;

/**
 * Abstract Transport Manager Details Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
abstract class AbstractTransportManagerDetailsController extends TransportManagerController
{
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
     * Process files
     *
     * @param Form $form
     * @param string $selector
     * @param string $uploadCallback
     * @param string $deleteCallback
     * @param string $loadCallback
     * @return bool
     */
    protected function processFiles($form, $selector, $uploadCallback, $deleteCallback, $loadCallback)
    {
        $uploadHelper = $this->getServiceLocator()->get('Helper\FileUpload');

        $uploadHelper->setForm($form)
            ->setSelector($selector)
            ->setUploadCallback($uploadCallback)
            ->setDeleteCallback($deleteCallback)
            ->setLoadCallback($loadCallback)
            ->setRequest($this->getRequest());

        return $uploadHelper->process();
    }

    /**
     * Upload a file
     *
     * @param array $fileData
     * @param array $data
     * @return array
     */
    protected function uploadFile($fileData, $data)
    {
        $uploader = $this->getServiceLocator()->get('FileUploader')->getUploader();
        $uploader->setFile($fileData);

        $file = $uploader->upload();

        $docData = array_merge(
            array(
                'filename'      => $file->getName(),
                'identifier'    => $file->getIdentifier(),
                'size'          => $file->getSize(),
                'fileExtension' => 'doc_' . $file->getExtension()
            ),
            $data
        );
        return $this->getServiceLocator()->get('Entity\Document')->save($docData);
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
