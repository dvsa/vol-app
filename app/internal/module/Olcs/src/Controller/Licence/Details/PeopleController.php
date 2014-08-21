<?php

/**
 * People Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * People Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class PeopleController extends AbstractLicenceDetailsController
{
    protected $section = 'people';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
