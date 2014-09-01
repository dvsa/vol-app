<?php

/**
 * Documents Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
 */
namespace Olcs\Controller\Licence\Details;

use Zend\View\Model\ViewModel;

/**
 * Documents Controller
 *
 * @author Jessica Rowbottom <jess.rowbottom@valtech.co.uk>
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
