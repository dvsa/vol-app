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

    /**
     * @codeCoverageIgnore
     */
    public function indexAction()
    {
    	
    	$contentView = new ViewModel();
        $contentView->setTemplate('index/home');

        return $this->renderView($contentView, 'Home', 'Subtitle');
    }

    private function renderView($view, $pageTitle, $pageSubTitle)
    {
    	$base = new ViewModel();
    	$base->setTemplate('layout/base.phtml');

    	$header = new ViewModel(['pageTitle' => $pageTitle, 'pageSubTitle' => $pageSubTitle]);
    	$header->setTemplate('layout/partials/header');

    	$base->setTerminal(true);
        $base->addChild($header, 'header');
        $base->addChild($view, 'content');

        return $base;
    }
}
