<?php

/**
 * Transport Manager Details Application & Licence Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace Olcs\Controller\TransportManager\Details;

use Olcs\Controller\TransportManager\Details\AbstractTransportManagerDetailsController;
use Zend\Navigation\Navigation;

/**
 * Transport Manager Details Application & Licence Controller
 * 
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsApplicationLicenceController extends AbstractTransportManagerDetailsController
{
    /**
     * @var string
     */
    protected $section = 'details-applications-licences';

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
