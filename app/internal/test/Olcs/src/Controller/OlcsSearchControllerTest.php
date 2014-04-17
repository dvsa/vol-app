<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class OlcsSearchControllerTest  extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../'
                . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\SearchController',
            array(
                'getServiceLocator',
                'setBreadcrumb',
                'generateFormWithData',
                'getPluginManager',
                'redirect',
                'params',
                'makeRestCall',
                'url'
            )
        );
        $this->serviceLocator = $this->getMock('\stdClass', array('get'));
        $this->pluginManager = $this->getMock('\stdClass', array('get'));
        $this->url = $this->getMock('\stdClass', array('fromRoute'));
        parent::setUp();
    }
    
    private function setServiceLocator($params, $returnVal) 
    {
        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceLocator));
        
        $this->serviceLocator->expects($this->once())
            ->method('get')
            ->with($params)
            ->will($this->returnValue($returnVal));
    }
    
    public function testIndexAction() 
    {
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array('search' => array()));
        
        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('search', 'processSearch')
            ->will($this->returnValue('zendForm'));
        
        $this->setServiceLocator('navigation', 'navigation');
        
        $this->controller->indexAction();
    }
    
     public function testProcessSearchAction() 
    {
        $data = array(
            'search' => [
                'licenceNumber' => '',
                'operatorName' => 'a',
                'postcode' => '',
                'firstName' => 'ken',
                'lastName' => '',
                'transportManagerId' => ''
            ],
            'search-advanced' => []
        );
        
        $redirect = $this->getMock('\stdClass', array('toUrl'));
        
        $this->controller->expects($this->once())
             ->method('url')
             ->will($this->returnValue($this->url));
        
        $this->url->expects($this->once())
            ->method('fromRoute')
            ->with('operators/operators-params', array ( 'operatorName' => 'a', 'firstName' => 'ken'))
            ->will($this->returnValue('/search/operators'));
        
        $this->controller->expects($this->once())
             ->method('redirect')
             ->will($this->returnValue($redirect));
        
        $redirect->expects($this->once())
            ->method('toUrl')
            ->with('/search/operators');

        $this->controller->processSearch($data);
    }
    
    public function testOperatorAction() 
    {
        $data = array ('controller' => 'SearchController',
            'action' => 'operator',
            'page' => 1,
            'limit' => 10,
            'operatorName' => 'a'
        );
        $this->url->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue($data));
        
        $this->controller->expects($this->once())
             ->method('url')
             ->will($this->returnValue('/search/operators'));
        
        $this->controller->expects($this->once())
             ->method('params')
             ->will($this->returnValue($this->url));
        
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('OperatorSearch', 'GET', $data)
            ->will($this->returnValue(array()));
        
        $tableBuilder = $this->getMock('\stdClass', array('buildTable'));
        
        $data['url'] = '/search/operators';
        $tableBuilder->expects($this->once())
            ->method('buildTable')
            ->with('operator', array(), $data)
            ->will($this->returnValue('table'));
        
        $this->setServiceLocator('Table', $tableBuilder);
        
        $this->controller->operatorAction();
    }
    
}
