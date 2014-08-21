<?php

/**
 * Address Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * Address Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class AddressController extends AbstractLicenceDetailsController
{
    protected $section = 'address';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
