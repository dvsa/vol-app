<?php

/**
 * OperatingCentre Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * OperatingCentre Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OperatingCentreController extends AbstractLicenceDetailsController
{
    protected $section = 'operating_centre';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
