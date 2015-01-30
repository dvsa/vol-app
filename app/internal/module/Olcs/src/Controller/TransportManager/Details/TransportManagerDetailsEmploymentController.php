<?php

/**
 * Transport Manager Details Employment Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Olcs\Controller\Traits\DeleteActionTrait;
use Zend\View\Model\ViewModel;
use Common\Service\Entity\ContactDetailsEntityService;

/**
 * Transport Manager Details Employment Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsEmploymentController extends AbstractTransportManagerDetailsController
{
    use DeleteActionTrait;

    /**
     * @var string
     */
    protected $section = 'details-employment';

    /**
     * @var string
     */
    protected $service = 'TmEmployment';

    /**
     * Index action
     *
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {
            $response = $this->checkForCrudAction();
            if ($response instanceof \Zend\Http\Response) {
                return $response;
            }
        }

        $this->loadScripts(['table-actions']);

        $table = $this->getEmploymentTable();
        $view = $this->getViewWithTm(['table' => $table->render()]);
        $view->setTemplate('pages/transport-manager/tm-competence');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    /**
     * Get employment table
     *
     * @return TableBuilder
     */
    protected function getEmploymentTable()
    {
        $transportManagerId = $this->params('transportManager');

        $results = $this->getServiceLocator()
            ->get('Entity\TmEmployment')
            ->getAllEmploymentsForTm($transportManagerId);

        $table = $this->getTable(
            'tm.employments',
            $results
        );
        return $table;
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
     * Handle form action
     *
     * @param string $type
     * @return Zend\View\Model\ViewModel
     */
    protected function formAction($type)
    {
        $id = $this->getFromRoute('id');
        $form = $this->getForm('tm-employment');
        if ($type == 'Edit') {
            $form = $this->populateEmploymentForm($form, $id);
        }

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
        return $this->renderView($view, $id ? 'Edit Other Employment' : 'Add Other Employment');
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
     * Populate employment form
     *
     * @param Form $form
     * @param int $id
     * @return Form
     */
    protected function populateEmploymentForm($form, $id)
    {
        $employment = $this->getServiceLocator()->get('Entity\TmEmployment')->getEmployment($id);
        $data = [
            'tm-employment-details' => [
                'id' => $employment['id'],
                'version' => $employment['version'],
                'position' => $employment['position'],
                'hoursPerWeek' => $employment['hoursPerWeek'],
            ],
            'tm-employer-name-details' => [
                'employerName' => $employment['employerName']
            ]
        ];

        if (isset($employment['contactDetails']['address'])) {
            $data['address'] = $employment['contactDetails']['address'];
        }
        $form->setData($data);
        return $form;
    }

    /**
     * Process form and redirect back to list
     *
     * @param array $data
     * @return redirect
     */
    protected function processForm($data)
    {
        if ($this->isButtonPressed('cancel')) {
            return $this->redirectToIndex();
        }

        $employment = $data['tm-employment-details'];

        $address = $data['address'];
        $addressData = $this->getServiceLocator()->get('Entity\Address')->save($address);
        if (isset($addressData['id'])) {
            $addressId = $addressData['id'];
            $contactDetails = [
                'address' => $addressId,
                'contactType' => ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER
            ];
            $contactDetailsData = $this->getServiceLocator()->get('Entity\ContactDetails')->save($contactDetails);
            $employment['contactDetails'] = $contactDetailsData['id'];
        }

        $tm = $this->getFromRoute('transportManager');
        $employment['transportManager'] = $tm;
        $employment['employerName'] = $data['tm-employer-name-details']['employerName'];

        $this->getServiceLocator()->get('Entity\TmEmployment')->save($employment);

        return $this->redirectToIndex();
    }
}
