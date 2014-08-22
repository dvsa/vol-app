<?php

/**
 * BusinessDetails Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * BusinessDetails Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class BusinessDetailsController extends AbstractLicenceDetailsController
{
    protected $section = 'business_details';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
