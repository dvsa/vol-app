<?php

/**
 * Operator Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\Operator;

use Olcs\Controller\AbstractController;

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
     * @codeCoverageIgnore
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
        $organisationService = $this->getServiceLocator()->get('Olcs\Service\Data\Organisation');
        $organisationId = $this->params()->fromRoute('operator');

        if ($organisationId) {
            $organisation = $organisationService->getOrganisation($organisationId, false);
            $this->pageTitle = isset($organisation['name']) ? $organisation['name'] : '';
            $variables['disable'] = false;
        } else {
            $organisation = null;
            $translator = $this->getServiceLocator()->get('translator');
            $this->pageTitle = $translator->translate('internal-operator-create-new-operator');
            $variables['disable'] = true;
        }
        $variables['organisation'] = $organisation;
        $variables['section'] = $this->section;

        $view = $this->getView($variables);

        return $view;
    }
}
