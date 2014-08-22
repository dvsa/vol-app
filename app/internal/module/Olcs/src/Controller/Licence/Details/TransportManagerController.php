<?php

/**
 * TransportManager Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * TransportManager Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TransportManagerController extends AbstractLicenceDetailsController
{
    protected $section = 'transport_manager';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
