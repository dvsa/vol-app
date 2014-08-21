<?php

/**
 * Safety Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * Safety Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class SafetyController extends AbstractLicenceDetailsController
{
    protected $section = 'safety';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
