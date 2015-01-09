<?php

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\AbstractController;
use Olcs\Controller\Interfaces\TransportManagerControllerInterface;

/**
 * Transport Manager Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerController extends AbstractController
    implements TransportManagerControllerInterface
{
    /**
     * @var string
     */
    protected $pageLayout = 'transport-manager-section';

    /**
     * Memoize TM details to prevent multiple backend calls with same id
     * @var array
     */
    protected $tmDetailsCache = [];

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
            $variables['disable'] = false;
        } else {
            $this->pageTitle =
                $this->getServiceLocator()
                ->get('translator')->translate('internal-transport-manager-new-transport-manager');
            $variables['disable'] = true;
        }

        $variables['section'] = $this->section;

        $view = $this->getView($variables);

        return $view;
    }

    public function getTmDetails($tmId, $bypassCache = false)
    {
        if ($bypassCache || !isset($this->tmDetailsCache[$tmId])) {
             $this->tmDetailsCache[$tmId] = $this->getServiceLocator()
                                                ->get('Entity\TransportManager')
                                                ->getTmDetails($tmId);
        }
        return $this->tmDetailsCache[$tmId];
    }
}
