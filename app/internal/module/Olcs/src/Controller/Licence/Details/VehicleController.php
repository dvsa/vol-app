<?php

/**
 * Vehicle Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * Vehicle Controller
 */
class VehicleController extends AbstractLicenceDetailsController
{
    protected $section = 'vehicle';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
