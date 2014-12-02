<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
class OlcsSearchControllerTest extends AbstractHttpControllerTestCase
{
    public function setUpAction($needMockRequest = false)
    {
        $this->setApplicationConfig(include __DIR__.'/../../../../config/application.config.php');

        $methods = [
            'getServiceLocator',
            'setBreadcrumb',
            'generateFormWithData',
            'getPluginManager',
            'redirect',
            'params',
            'makeRestCall',
            'url',
            'getTable',
            'getSearchForm'
        ];
        if ($needMockRequest) {
            $methods[] = 'getRequest';
        }
        $this->controller = $this->getMock(
            '\Olcs\Controller\SearchController', $methods
        );
        $this->serviceLocator = $this->getMock('\stdClass', array('get'));
        $this->pluginManager = $this->getMock('\stdClass', array('get'));
        $this->url = $this->getMock('\stdClass', array('fromRoute'));
        parent::setUp();
    }

    public function testIndexAction()
    {
        $this->setUpAction();
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array('search' => array()));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('search', 'processSearch')
            ->will($this->returnValue('zendForm'));

        $this->controller->advancedAction();
    }

    public function testProcessSearchAction()
    {
        $this->setUpAction();
        $data = array(
            'search' => [
                'licNo' => '',
                'operatorName' => 'a',
                'postcode' => '',
                'forename' => 'ken',
                'familyName' => '',
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
            ->with('operators/operators-params', array ( 'operatorName' => 'a', 'forename' => 'ken'))
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
        $this->setUpAction();
        $data = array ('controller' => 'SearchController',
            'action' => 'operator',
            'page' => 1,
            'limit' => 10,
            'operatorName' => 'a'
        );

        $results = $this->getSampleOperatorResults();

        $this->url->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue($data));

        $this->controller->expects($this->once())
             ->method('params')
             ->will($this->returnValue($this->url));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('OperatorSearch', 'GET', $data)
            ->will($this->returnValue($results));

        $configServiceLocator = $this->getMock('\stdClass', array('get'));

        $configServiceLocator->expects($this->once())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue($this->getStaticEntityTypes()));

        $this->controller->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue('table'));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($configServiceLocator));

        $this->controller->operatorAction();
    }

    /**
     * Test operator action with redirect
     */
    public function testOperatorWithRedirectAction()
    {
        $this->setUpAction(true);

        $mockRequest = $this->getMock('\StdClass', ['getPost']);
        $mockRequest->expects($this->once())
            ->method('getPost')
            ->will(
                $this->returnValue(
                    [
                        'action' => 'Create operator'
                    ]
                )
            );

        $mockRedirect = $this->getMock('\StdClass', ['toRoute']);
        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('response'));

        $this->controller->expects($this->once())
             ->method('redirect')
             ->will($this->returnValue($mockRedirect));

        $this->controller->expects($this->once())
             ->method('getRequest')
             ->will($this->returnValue($mockRequest));

        $response = $this->controller->operatorAction();
        $this->assertEquals('response', $response);
    }

    /**
     * Test operator action with redirect to create TM
     */
    public function testOperatorWithRedirectToTmAction()
    {
        $this->setUpAction(true);

        $mockRequest = $this->getMock('\StdClass', ['getPost']);
        $mockRequest->expects($this->once())
            ->method('getPost')
            ->will(
                $this->returnValue(
                    [
                        'action' => 'Create transport manager'
                    ]
                )
            );

        $mockRedirect = $this->getMock('\StdClass', ['toRoute']);
        $mockRedirect->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue('response'));

        $this->controller->expects($this->once())
             ->method('redirect')
             ->will($this->returnValue($mockRedirect));

        $this->controller->expects($this->once())
             ->method('getRequest')
             ->will($this->returnValue($mockRequest));

        $response = $this->controller->operatorAction();
        $this->assertEquals('response', $response);
    }

    private function getStaticEntityTypes()
    {
        return array(
            'static-list-data' => array(
                'business_types' =>
                [
                    'org_t_rc' => 'Limited company',
                    'org_t_st' => 'Sole Trader',
                    'org_t_p' => 'Partnership',
                    'org_t_llp' => 'Limited Liability Partnership',
                    'org_t_pa' => 'Other (e.g. public authority, charity, trust, university)',
                ],
            )
        );
    }

    private function getSampleOperatorResults()
    {
        return array('Results' => array(array('organisation_type' => 'org_t_rc')));
    }
}
