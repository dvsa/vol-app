<?php

/**
 * Transport Manager Details Detail Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;

/**
 * Transport Manager Details Detail Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsDetailController extends AbstractTransportManagerDetailsController
{
    /**
     * @var string
     */
    protected $section = 'details-details';

    /**
     * Index action
     *
     * @return ViewModel
     */
    public function indexAction()
    {
        $form = $this->getForm('TransportManager');
        $view = $this->getViewWithTm(['form' => $form]);
        $view->setTemplate('transport-manager/details/tm-details');
        return $this->renderView($view);
    }
}
