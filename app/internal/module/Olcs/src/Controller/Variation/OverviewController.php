<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Variation;

use Zend\View\Model\ViewModel;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractVariationController
{
    /**
     * Application overview
     */
    public function indexAction()
    {
        // @NOTE until we know more about the variation section, this will use the application views
        $content = new ViewModel();
        $content->setTemplate('application/overview');

        return $this->render($content);
    }
}
