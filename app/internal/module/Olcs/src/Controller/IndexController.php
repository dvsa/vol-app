<?php

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
namespace Olcs\Controller;

use Common\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

/**
 * IndexController
 *
 * @author Mike Cooper <michael.cooper@valtech.co.uk>
 */
class IndexController extends AbstractActionController
{
    protected $pageTitle = 'Home';
    protected $pageSubTitle = 'Subtitle';

    /**
     * @codeCoverageIgnore
     */
    public function indexAction()
    {
        return $this->renderView('index/home');
    }
}
