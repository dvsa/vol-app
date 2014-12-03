<?php

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

use Olcs\Controller\AbstractController;
use Zend\View\Model\ViewModel;

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorController extends AbstractController
{
    /**
     * @var string
     */
    protected $pageLayout = 'operator';

    /**
     * Redirect to the first menu section
     *
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('operator/business-details', [], [], true);
    }

    /**
     * Get view with Operator
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithOrganisation($variables = [])
    {
        $organisationId = $this->params()->fromRoute('operator');

        if ($organisationId) {
            $org = $this->getServiceLocator()->get('Entity\Organisation')->getBusinessDetailsData($organisationId);
            $this->pageTitle = isset($org['name']) ? $org['name'] : '';
            $variables['disable'] = false;
        } else {
            $org = null;
            $translator = $this->getServiceLocator()->get('translator');
            $this->pageTitle = $translator->translate('internal-operator-create-new-operator');
            $variables['disable'] = true;
        }
        $variables['organisation'] = $org;
        $variables['section'] = $this->section;

        $view = $this->getView($variables);

        return $view;
    }

    public function newApplicationAction()
    {
        $this->pageLayout = null;

        $request = $this->getRequest();

        if ($request->isPost()) {
            $data = (array)$request->getPost();
        } else {
            $data['receivedDate'] = $this->getServiceLocator()->get('Helper\Date')->getDateObject();
        }

        $formHelper = $this->getServiceLocator()->get('Helper\Form');

        $form = $formHelper->createForm('NewApplication');
        $form->setData($data);

        $formHelper->setFormActionFromRequest($form, $this->getRequest());

        if ($request->isPost() && $form->isValid()) {

            $data = $form->getData();

            $created = $this->getServiceLocator()->get('Entity\Application')
                ->createNew($this->params('operator'), array('receivedDate' => $data['receivedDate']));

            return $this->redirect()->toRouteAjax(
                'lva-application/type_of_licence',
                ['application' => $created['application']]
            );
        }

        $view = new ViewModel(['form' => $form]);
        $view->setTemplate('form-simple');

        return $this->renderView($view, 'Create new application');
    }
}
