<?php

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\Controller\Lva\Variation;

use Zend\View\Model\ViewModel;
use Common\Controller\Lva\AbstractController;
use Olcs\Controller\Lva\Traits\VariationControllerTrait;

/**
 * Application Overview Controller
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class OverviewController extends AbstractController
{
    use VariationControllerTrait;

    protected $lva = 'variation';
    protected $location = 'internal';

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
