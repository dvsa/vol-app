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
            array($this, 'deleteTmFile'),
            array($this, 'getDocuments')
        );

        $view = $this->getViewWithTm(['table' => $table->render(), 'form' => $form]);
        $view->setTemplate('pages/transport-manager/tm-competence');
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
                null,
                null,
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
