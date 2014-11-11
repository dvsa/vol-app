<?php

/**
 * Transport Manager Document Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager;

use Olcs\Controller\TransportManager\TransportManagerController;

/**
 * Transport Manager Document Controller
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDocumentController extends TransportManagerController
{
    /**
     * @var string
     */
    protected $section = 'documents';

    /**
     * Placeholder stub
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $view = $this->getViewWithTm();
        $view->setTemplate('transport-manager/index');
        return $this->renderView($view);
    }
}
