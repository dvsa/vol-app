<?php

/**
 * TaxiPhv Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * TaxiPhv Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TaxiPhvController extends AbstractLicenceDetailsController
{
    protected $section = 'taxi_phv';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
