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
    public function setUp()
    {
        $this->setApplicationConfig(include __DIR__.'/../../../../config/application.config.php');

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

        $this->controller->indexAction();
    }

    public function testProcessSearchAction()
    {
        $data = array(
            'search' => [
                'licNo' => '',
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

        $results = $this->getSampleOperatorResults();
        $processedResults = $this->getProcessedOperatorResults();

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
            ->will($this->returnValue($results));

        $tableBuilder = $this->getMock('\stdClass', array('buildTable'));

        $data['url'] = '/search/operators';

        $configServiceLocator = $this->getMock('\stdClass', array('get'));

        $configServiceLocator->expects($this->once())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue($this->getStaticEntityTypes()));

        $serviceLocator = $this->getMock('\stdClass', array('get'));
        $tableBuilder = $this->getMock('\stdClass', array('buildTable'));

        $tableBuilder->expects($this->once())
            ->method('buildTable')
            ->with('operator', $processedResults, $data)
            ->will($this->returnValue('table'));

        $serviceLocator->expects($this->once())
            ->method('get')
            ->with('Table')
            ->will($this->returnValue($tableBuilder));

        $this->controller->expects($this->exactly(2))
            ->method('getServiceLocator')
            ->will(
                $this->onConsecutiveCalls(
                    $configServiceLocator,
                    $serviceLocator
                )
            );

        $this->controller->operatorAction();
    }

    private function getStaticEntityTypes()
    {
        return array(
            'static-list-data' => array(
                'business_types' =>
                [
                    'org_type.lc' => 'Limited company',
                    'org_type.st' => 'Sole Trader',
                    'org_type.p' => 'Partnership',
                    'org_type.llp' => 'Limited Liability Partnership',
                    'org_type.o' => 'Other (e.g. public authority, charity, trust, university)',
                ],
            )
        );
    }

    private function getSampleOperatorResults()
    {
        return array(
            'Results' => array(
                0 =>
                [
                    'organisation_type' => 'org_type.lc'
                ]
            )
        );
    }

    private function getProcessedOperatorResults()
    {
        return array(
            'Results' => array(
                0 =>
                [
                    'organisation_type' => 'Limited company'
                ]
            )
        );
    }
}
