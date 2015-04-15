<?php

/**
 * Transport Manager Details Previous History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Zend\View\Model\ViewModel;

/**
 * Transport Manager Details Previous History Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsPreviousHistoryController extends AbstractTransportManagerDetailsController
{
    /**
     * @var string
     */
    protected $section = 'details-previous-history';

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

        //$this->loadScripts(['lva-crud']);

        $form = $this->getPreviousHistoryForm();

        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('pages/form');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());
        return $this->renderView($view);
    }

    protected function getPreviousHistoryForm()
    {
        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('TmPreviousHistory');

        $this->getServiceLocator()->get('Helper\TransportManager')
            ->alterPreviousHistoryFieldset($form->get('previousHistory'), $this->params('transportManager'));

        return $form;
    }

    /**
     * Delete previous conviction action
     */
    public function deletePreviousConvictionAction()
    {
        return $this->deleteRecords('Entity\PreviousConviction');
    }

    /**
     * Delete previous licence action
     */
    public function deletePreviousLicenceAction()
    {
        return $this->deleteRecords('Entity\OtherLicence');
    }

    /**
     * Add previous conviction action
     *
     * @return mixed
     */
    public function previousConvictionAddAction()
    {
        return $this->formAction('Add', 'tm-convictions-and-penalties');
    }

    /**
     * Edit previous conviction action
     *
     * @return mixed
     */
    public function editPreviousConvictionAction()
    {
        return $this->formAction('Edit', 'tm-convictions-and-penalties');
    }

    /**
     * Add previous licence action
     *
     * @return mixed
     */
    public function previousLicenceAddAction()
    {
        return $this->formAction('Add', 'tm-previous-licences');
    }

    /**
     * Edit previous licence action
     *
     * @return mixed
     */
    public function editPreviousLicenceAction()
    {
        return $this->formAction('Edit', 'tm-previous-licences');
    }

    /**
     * Form action
     *
     * @param string $type
     * @param string $formName
     * @return mixed
     */
    protected function formAction($type, $formName)
    {
        $form = $this->getForm($formName);
        $request = $this->getRequest();
        if ($request->isPost()) {
            if ($this->isButtonPressed('cancel')) {
                return $this->redirectToIndex();
            }
        }

        $form = $this->alterForm($form, $type);

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('partials/form');

        if (!$this->getRequest()->isPost()) {
            $form = $this->populateEditForm($form);
        }
        $this->formPost($form, 'processForm');
        if ($this->getResponse()->getContent() !== "") {
            return $this->getResponse();
        }
        return $this->renderView(
            $view,
            $type . (($formName == 'tm-convictions-and-penalties') ? ' previous conviction' : ' previous licence')
        );
    }

    /**
     * Alter form
     *
     * @param Zend\Form\Form $form
     * @param string $type
     * @return Zend\Form\Form
     */
    protected function alterForm($form, $type)
    {
        if ($type !== 'Add') {
            $this->getServiceLocator()->get('Helper\Form')->remove($form, 'form-actions->addAnother');
        }
        return $form;
    }

    /**
     * Populate edit form
     *
     * @param Zend\Form\Form $form
     * @return Zend\Form\Form
     */
    protected function populateEditForm($form)
    {
        $formName = $form->getName();
        $id = $this->getFromRoute('id');
        if ($formName == 'tm-convictions-and-penalties') {
            $data = $this->getServiceLocator()->get('Entity\PreviousConviction')->getById($id);
            $dataPrepared = [
                'tm-convictions-and-penalties-details' => $data
            ];
        } else {
            $data = $this->getServiceLocator()->get('Entity\OtherLicence')->getById($id);
            $dataPrepared = [
                'tm-previous-licences-details' => $data
            ];
        }
        $form->setData($dataPrepared);
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
        $tm = $this->getFromRoute('transportManager');
        if (isset($data['tm-convictions-and-penalties-details'])) {
            $dataPrepared = $data['tm-convictions-and-penalties-details'];
            $serviceName = 'Entity\PreviousConviction';
            $action = 'previous-conviction-add';
        } else {
            $dataPrepared = $data['tm-previous-licences-details'];
            $serviceName = 'Entity\OtherLicence';
            $action = 'previous-licence-add';
        }
        $dataPrepared['transportManager'] = $tm;
        $this->getServiceLocator()->get($serviceName)->save($dataPrepared);
        if ($this->isButtonPressed('addAnother')) {
            $routeParams = [
                'transportManager' => $this->fromRoute('transportManager'),
                'action' => $action
            ];
            return $this->redirect()->toRoute(null, $routeParams);
        } else {
            return $this->redirectToIndex();
        }
    }
}
