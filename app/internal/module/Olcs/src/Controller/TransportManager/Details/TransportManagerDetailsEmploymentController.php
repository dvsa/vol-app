<?php

/**
 * Transport Manager Details Employment Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Zend\View\Model\ViewModel;
use Common\Controller\Lva\Traits\CrudActionTrait;

/**
 * Transport Manager Details Employment Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsEmploymentController extends AbstractTransportManagerDetailsController
{
    use CrudActionTrait;

    /**
     * @var string
     */
    protected $section = 'details-employment';

    /**
     * Index action
     *
     * @return ViewModel|Response
     */
    public function indexAction()
    {
        $request = $this->getRequest();

        if ($request->isPost()) {

            $data = (array)$request->getPost();

            if (isset($data['employment'])) {
                $crudAction = $this->getCrudAction([$data['employment']]);

                if ($crudAction !== null) {
                    return $this->handleCrudAction($crudAction, ['add-employment'], 'id');
                }
            }
        }

        $form = $this->getEmploymentForm();

        $this->loadScripts(['forms/crud-table-handler', 'tm-other-employment']);

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('pages/form');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());

        return $this->renderView($view);
    }

    protected function getEmploymentForm()
    {
        $form = $this->getServiceLocator()->get('Helper\Form')
            ->createForm('TmOtherEmployment');

        $transportManagerId = $this->params('transportManager');

        $this->getServiceLocator()->get('Helper\TransportManager')
            ->prepareOtherEmploymentTable($form->get('otherEmployment'), $transportManagerId);

        return $form;
    }

    /**
     * Add action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function addEmploymentAction()
    {
        return $this->formAction('Add');
    }

    /**
     * Edit action
     *
     * @return Zend\View\Model\ViewModel
     */
    public function editEmploymentAction()
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
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'form-actions->addAnother');
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
     * Populate employment form
     *
     * @param Form $form
     * @param int $id
     * @return Form
     */
    protected function populateEmploymentForm($form, $id)
    {
        $data = $this->getServiceLocator()->get('Helper\TransportManager')->getOtherEmploymentData($id);
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
        $employment['transportManager'] = $this->getFromRoute('transportManager');
        $employment['employerName'] = $data['tm-employer-name-details']['employerName'];

        $params = [
            'address' => $data['address'],
            'data' => $employment
        ];

        $this->getServiceLocator()->get('BusinessServiceManager')
            ->get('TmEmployment')
            ->process($params);

        if ($this->isButtonPressed('addAnother')) {
            $routeParams = [
                'transportManager' => $this->getFromRoute('transportManager'),
                'action' => 'add-employment'
            ];
            return $this->redirect()->toRoute(null, $routeParams);
        } else {
            return $this->redirectToIndex();
        }
    }

    /**
     * Delete action
     */
    public function deleteEmploymentAction()
    {
        return $this->deleteRecords('Entity\TmEmployment');
    }
}
