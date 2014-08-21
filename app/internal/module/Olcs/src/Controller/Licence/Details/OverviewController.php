<?php

/**
 * Overview Controller
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractLicenceDetailsController
{
    protected $section = 'overview';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/details/placeholder');

        return $this->renderView($view);
    }
}
