<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Application;

use Zend\View\Model\ViewModel;
use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\ApplicationControllerTrait;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController
{
    use ApplicationControllerTrait;

    protected $lva = 'application';
    protected $location = 'internal';

    /**
     * Application overview
     */
    public function indexAction()
    {
        $content = new ViewModel();
        $content->setTemplate('pages/placeholder');

        return $this->render($content);
    }
}
