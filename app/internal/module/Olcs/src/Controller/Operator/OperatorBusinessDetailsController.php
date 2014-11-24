<?php

/**
 * Operator Business Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

/**
 * Operator Business Details Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorBusinessDetailsController extends OperatorController
{
    /**
     * @var string
     */
    protected $section = 'business_details';

    /**
     * @var bool
     */
    protected $saved = false;

    /**
     * Index action
     *
     * @return \Zend\View\Model\ViewModel
     */
    public function indexAction()
    {
        $operator = $this->params()->fromRoute('operator');

        if ($this->isButtonPressed('cancel')) {
            // user pressed cancel button in edit form
            if ($operator) {
                $this->flashMessenger()->addSuccessMessage('Your changes have been discarded');
                return $this->redirectToRoute('operator/business-details', ['operator' => $operator]);
            } else {
                // user pressed cancel button in add form
                return $this->redirectToRoute('operators/operators-params');
            }
        }

        $form = $this->getForm('operator');
        if (!$operator) {
            $action = 'Add';
        } else {
            $action = 'Edit';
            $form = $this->setOrganisationDetails($form, $operator);
        }
        $this->formPost($form, 'process'  . $action . 'Organisation');
        // we need to process redirect and catch flashMessenger messages if available
        if ($this->getResponse()->getStatusCode() == 302) {
            return $this->getResponse();
        }

        if ($this->saved) {
            // need to reload form, to update version and all other fields
            $form = $this->setOrganisationDetails($form, $operator);
        }

        $view = $this->getViewWithOrganisation(['form' => $form]);
        $view->setTemplate('operator/business-details/index');
        return $this->renderView($view);
    }

    /**
     * Set organisation details
     *
     * @param \Zend\Form\Form $form
     * @param int $operator
     * @return \Zend\Form\Form
     */
    protected function setOrganisationDetails($form, $operator)
    {
        $operatorService = $this->getServiceLocator()->get('Olcs\Service\Data\Organisation');
        $data = $operatorService->getOrganisation($operator, false);
        $this->setDataOperatorForm($form, $data);
        return $form;
    }

    /**
     * Set data
     *
     * @param Zend\Form\Form $form
     * @param array $data
     * @return Zend\Form\Form
     */
    protected function setDataOperatorForm($form, $data = [])
    {
        if ($form) {
            $form->get('operator-details')->get('id')->setValue($data['id']);
            $form->get('operator-details')->get('version')->setValue($data['version']);
            $form->get('operator-details')->get('name')->setValue($data['name']);
        }
        return $form;
    }

    /**
     * Process edit organisation
     *
     * @param array $data
     */
    protected function processEditOrganisation($data)
    {
        $params = $data['operator-details'];
        $organisationService = $this->getServiceLocator()->get('Olcs\Service\Data\Organisation');
        $organisationService->updateOrganisation($params);
        $this->flashMessenger()->addSuccessMessage('The operator has been updated successfully');
        $this->saved = true;
    }

    /**
     * Process add organisation
     *
     * @param array $data
     */
    protected function processAddOrganisation($data = [])
    {
        $params = $data['operator-details'];
        $params['type'] = 'org_t_rc';
        $params['createdBy'] = $this->getLoggedInUser();
        $organisationService = $this->getServiceLocator()->get('Olcs\Service\Data\Organisation');
        $newOperator = $organisationService->createOrganisation($params);
        $this->flashMessenger()->addSuccessMessage('The operator has been created successfully');
        return $this->redirectToRoute('operator/business-details', ['operator' => $newOperator['id']]);
    }
}
