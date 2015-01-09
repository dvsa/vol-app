<?php

/**
 * Transport Manager Details Competence Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Zend\View\Model\ViewModel;
use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Olcs\Controller\Traits\DeleteActionTrait;
use Common\Service\Data\CategoryDataService;

/**
 * Transport Manager Details Competence Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsCompetenceController extends AbstractTransportManagerDetailsController
{
    use DeleteActionTrait;

    /**
     * @var string
     */
    protected $section = 'details-competences';

    /**
     * @var string
     */
    protected $service = 'TmQualification';

    /**
     * Index action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function indexAction()
    {

        $table = $this->getQualificationsTable();

        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
            $this->checkForCrudAction();
        }

        $this->loadScripts(['table-actions']);


        $form = $this->getForm('certificate-upload');
        $this->processFiles(
            $form,
            'file',
            array($this, 'processCertificateFileUpload'),
            array($this, 'deleteCertificateFile'),
            array($this, 'getDocuments')
        );

        $view = $this->getViewWithTm(['table' => $table->render(), 'form' => $form]);
        $view->setTemplate('pages/tm-competence');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    /**
     * Add action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function addAction()
    {
        return $this->formAction('Add');
    }

    /**
     * Edit action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function editAction()
    {
        return $this->formAction('Edit');
    }

    /**
     * Get qualifications table
     *
     * @return TableBuilder
     */
    protected function getQualificationsTable()
    {
        $transportManagerId = $this->params('transportManager');
        $qualifications =
            $this->getServiceLocator()->get('Entity\TmQualification')->getQualificationsForTm($transportManagerId);

        $table = $this->getTable(
            'tm.qualifications',
            $qualifications
        );

        return $table;
    }

    /**
     * Handle form action
     *
     * @param string $type
     * @return Zend\View\Model\ViewModel
     */
    protected function formAction($type)
    {
        $form = $this->getForm('qualification');

        $id = $this->getFromRoute('id');
        $form = $this->populateQualificationForm($form, $id);

        $this->formPost($form, 'processForm');
        if ($this->getResponse()->getContent() !== "") {
            return $this->getResponse();
        }

        $view = new ViewModel(
            [
               'form' => $form
            ]
        );


        $view->setTemplate('partials/form');
        return $this->renderView($view, $id ? 'Edit qualification' : 'Add qualification');
    }

    /**
     * Process form and redirect back to list
     *
     * @param array $data
     * @return redirect
     */
    protected function processForm($data)
    {
        $tm = $this->getFromRoute('transportManager');
        $qualification = $data['qualification-details'];
        $qualification['transportManager'] = $tm;

        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $this->getServiceLocator()->get('Entity\TmQualification')->save($qualification);

        return $this->redirectToIndex();
    }

    /**
     * Handle form action
     *
     * @param int $id
     * @param Form $form
     * @return Form
     */
    protected function populateQualificationForm($form, $id = null)
    {
        if ($id) {
            $qualification = $this->getServiceLocator()->get('Entity\TmQualification')->getQualification($id);
            $data = [
                'qualification-details' => [
                    'id' => $qualification['id'],
                    'version' => $qualification['version'],
                    'issuedDate' => $qualification['issuedDate'],
                    'serialNo' => $qualification['serialNo'],
                    'qualificationType' => $qualification['qualificationType']['id'],
                    'countryCode' => $qualification['countryCode']['id']
                ]
            ];
        } else {
            $data = [
                'qualification-details' => [
                    'countryCode' => 'GB'
                ]
            ];
        }
        $form->setData($data);
        return $form;
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

    /**
     * Get delete service name
     *
     * @return string
     */
    public function getDeleteServiceName()
    {
        return $this->service;
    }

    /**
     * Delete file
     *
     * @NOTE This is public so it can be called as a callback when processing files
     *
     * @param int $id
     * @return Redirect
     */
    public function deleteCertificateFile($id)
    {
        $documentService = $this->getServiceLocator()->get('Entity\Document');

        $identifier = $documentService->getIdentifier($id);

        if (!empty($identifier)) {
            $this->getServiceLocator()->get('FileUploader')->getUploader()->remove($identifier);
        }

        $documentService->delete($id);
        return $this->redirectToIndex();
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
     * Get transport manager documents
     *
     * @return array
     */
    public function getDocuments()
    {
        $tmId = $this->getFromRoute('transportManager');
        return $this->getServiceLocator()->get('Entity\TransportManager')
            ->getDocuments(
                $tmId,
                CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
            );
    }

    /**
     * Handle the file upload
     *
     * @param array $file
     * @return array
     */
    public function processCertificateFileUpload($file)
    {
        $tmId = $this->getFromRoute('transportManager');
        return $this->uploadFile(
            $file,
            array(
                'transportManager' => $tmId,
                'description' => $file['name'],
                'category'    => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                'subCategory' => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
            )
        );
    }
}
