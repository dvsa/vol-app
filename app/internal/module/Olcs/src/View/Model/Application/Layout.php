<?php

/**
 * Layout
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
namespace Olcs\View\Model\Application;

use Zend\View\Model\ViewModel;
use Common\View\AbstractViewModel;

/**
 * Layout
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class Layout extends AbstractViewModel
{
    protected $template = 'layout/base';

    protected $terminate = true;

    public function __construct($content, $quickActions, array $params = array())
    {
        $header = new ViewModel();
        $header->setTemplate('application/header');

        $this->addChild($header, 'header');

        $applicationLayout = new ViewModel();
        $applicationLayout->setTemplate('application/layout');

        $mainNav = new ViewModel();
        $mainNav->setTemplate('application/main-nav');

        $applicationLayout->addChild($mainNav, 'mainNav');
        $applicationLayout->addChild($quickActions, 'quickActions');
        $applicationLayout->addChild($content, 'content');
    }
}
