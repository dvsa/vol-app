<?php

/**
 * Internal Licence Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Licence;

use Zend\View\Model\ViewModel;

/**
 * Internal Licence Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractLicenceController
{
    /**
     * Licence overview
     */
    public function indexAction()
    {
        $content = new ViewModel();
        $content->setTemplate('licence/overview');

        return $this->render($content);
    }
}
