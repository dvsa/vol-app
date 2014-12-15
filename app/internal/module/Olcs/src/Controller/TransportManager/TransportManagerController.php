<?php

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\AbstractController;

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerController extends AbstractController
{
    /**
     * @var string
     */
    protected $pageLayout = 'transport-manager-section';

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexJumpAction()
    {
        return $this->redirect()->toRoute('transport-manager/details/details', [], [], true);
    }

    /**
     * Redirect to the first menu section
     *
     * @codeCoverageIgnore
     * @return \Zend\Http\Response
     */
    public function indexProcessingJumpAction()
    {
        return $this->redirect()->toRoute('transport-manager/processing/notes', [], [], true);
    }

    /**
     * Get view with TM
     *
     * @param array $variables
     * @return \Zend\View\Model\ViewModel
     */
    protected function getViewWithTm($variables = [])
    {
        $tmId = $this->params()->fromRoute('transportManager');
        if ($tmId) {
            $transportManager = $this->getServiceLocator()->get('Entity\TransportManager')->getTmDetails($tmId);
            $this->pageTitle = isset($transportManager['contactDetails']['person']['forename']) ?
                $transportManager['contactDetails']['person']['forename'] . ' ': '';
            $this->pageTitle .= isset($transportManager['contactDetails']['person']['familyName']) ?
                $transportManager['contactDetails']['person']['familyName'] : '';
            $variables['disable'] = false;
        } else {
            $transportManager = null;
            $this->pageTitle =
                $this->getServiceLocator()
                ->get('translator')->translate('internal-transport-manager-new-transport-manager');
            $variables['disable'] = true;
        }

        $variables['transportManager'] = $transportManager;
        $variables['section'] = $this->section;

        $view = $this->getView($variables);

        return $view;
    }
}
