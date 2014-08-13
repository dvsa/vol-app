<?php

/**
 * Description of OlcsIndexControllerTest
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller;

class OlcsIndexControllerTest  extends \PHPUnit_Framework_TestCase
{
    public function testIndexActionAssignsCorrectTitles()
    {
        $controller = new \Olcs\Controller\IndexController();
        $view = $controller->indexAction();

        list($header, $content) = $view->getChildren();

        $titles = array(
            'pageTitle' => 'Home',
            'pageSubTitle' => 'Subtitle'
        );
        $this->assertEquals($titles, $header->getVariables());
    }
}
