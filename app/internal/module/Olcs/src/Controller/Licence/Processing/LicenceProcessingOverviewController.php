<?php

/**
 * Overview Controller
 */
namespace Olcs\Controller\Licence\Processing;

use Zend\View\Model\ViewModel;

/**
 * Overview Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class OverviewController extends AbstractLicenceProcessingController
{
    protected $section = 'overview';

    public function indexAction()
    {
        $view = new ViewModel();
        $view->setTemplate('licence/processing/placeholder');

        return $this->renderView($view);
    }
}
