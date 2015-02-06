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

        $this->loadScripts(['table-actions']);

        $convictionsAndPenaltiesTable = $this->getConvictionsAndPenaltiesTable();
        $previousLicencesTable = $this->getPreviousLicencesTable();

        $view = $this->getViewWithTm(
            ['topTable' => $convictionsAndPenaltiesTable->render(), 'bottomTable' => $previousLicencesTable->render()]
        );

        $view->setTemplate('pages/transport-manager/tm-2-tables');
        $view->setTerminal($this->getRequest()->isXmlHttpRequest());
        return $this->renderView($view);
    }

    /**
     * Get convictions & penalties table
     *
     * @return TableBuilder
     */
    protected function getConvictionsAndPenaltiesTable()
    {
        $transportManagerId = $this->params('transportManager');

        $results = $this->getServiceLocator()
            ->get('Entity\PreviousConviction')
            ->getDataForTransportManager($transportManagerId);

        $table = $this->getTable(
            'tm.convictionsandpenalties',
            $results
        );
        return $table;
    }

    /**
     * Get previous licences table
     *
     * @return TableBuilder
     */
    protected function getPreviousLicencesTable()
    {
        $transportManagerId = $this->params('transportManager');

        $results = $this->getServiceLocator()
            ->get('Entity\OtherLicence')
            ->getDataForTransportManager($transportManagerId);

        $table = $this->getTable(
            'tm.previouslicences',
            $results
        );
        return $table;
    }

    /**
     * Delete previous conviction action
     */
    public function deletePreviousConvictionAction()
    {
        return $this->deletePreviousHistoryRecord('Entity\PreviousConviction');
    }

    /**
     * Delete previous licence action
     */
    public function deletePreviousLicenceAction()
    {
        return $this->deletePreviousHistoryRecord('Entity\OtherLicence');
    }

    /**
     * Delete previous conviction or previous licence
     * 
     * @param string $serviceName
     * @param string $childServiceName
     * @return Redirect
     */
    protected function deletePreviousHistoryRecord($serviceName)
    {
        $id = $this->getFromRoute('id');
        $response = $this->confirm(
            'Are you sure you want to permanently delete this record?'
        );

        if ($response instanceof ViewModel) {
            return $this->renderView($response);
        }
        if (!$this->isButtonPressed('cancel')) {
            $this->getServiceLocator()->get($serviceName)->delete($id);
            $this->addSuccessMessage('Deleted successfully');
        }
        return $this->redirectToIndex();
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
            $data = $this->getServiceLocator()->get('Entity\PreviousConviction')->getData($id);
            $dataPrepared = [
                'tm-convictions-and-penalties-details' => $data
            ];
        } else {
            $data = $this->getServiceLocator()->get('Entity\OtherLicence')->getData($id);
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
        } else {
            $dataPrepared = $data['tm-previous-licences-details'];
            $serviceName = 'Entity\OtherLicence';
        }
        $dataPrepared['transportManager'] = $tm;
        $this->getServiceLocator()->get($serviceName)->save($dataPrepared);
        return $this->redirectToIndex();
    }
}
