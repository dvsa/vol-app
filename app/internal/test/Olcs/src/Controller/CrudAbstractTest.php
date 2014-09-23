<?php

/**
 * Case Stay Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Tests the case stay controller
 */
class CrudAbstractTest extends AbstractHttpControllerTestCase
{
    protected $traitsRequired = array(
        'Zend\Log\LoggerAwareTrait',
        'Common\Util\LoggerTrait',
        'Common\Util\FlashMessengerTrait',
        'Common\Util\RestCallTrait',
        'Common\Controller\Traits\ViewHelperManagerAware',
        'Olcs\Controller\Traits\DeleteActionTrait'
    );

    protected $testClass = '\Olcs\Controller\CrudAbstract';

    /**
     * Tests that the method returns value from getQuery method.
     */
    public function testGetQueryOrRouteParamReturnsQueryParam()
    {
        $name = 'name';
        $value = 'value';

        $params = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', ['fromQuery']);
        $params->expects($this->any())->method('fromQuery')
               ->with($name, null)
               ->will($this->returnValue($value));

        $sut = $this->getSutForIsolatedTest(['params']);
        $sut->expects($this->any())->method('params')->will($this->returnValue($params));

        $this->assertEquals($value, $sut->getQueryOrRouteParam($name, null));
    }

    /**
     * Tests that the method returns value from getRoute method.
     */
    public function testGetQueryOrRouteParamReturnsRouteParam()
    {
        $name = 'name';
        $value = 'value';

        $params = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', ['fromQuery', 'fromRoute']);
        $params->expects($this->any())->method('fromQuery')->will($this->returnValue(null));
        $params->expects($this->any())->method('fromRoute')
               ->with($name, null)
               ->will($this->returnValue($value));

        $sut = $this->getSutForIsolatedTest(['params']);
        $sut->expects($this->any())->method('params')->will($this->returnValue($params));

        $this->assertEquals($value, $sut->getQueryOrRouteParam($name, null));
    }

    /**
     * Tests that the method returns the default value
     */
    public function testGetQueryOrRouteParamReturnsDefaultParam()
    {
        $name = 'name';
        $default = 'default';

        $params = $this->getMock('\Zend\Mvc\Controller\Plugin\Params', ['fromQuery', 'fromRoute']);
        $params->expects($this->any())->method('fromQuery')->will($this->returnValue(null));
        $params->expects($this->any())->method('fromRoute')->will($this->returnValue(null));

        $sut = $this->getSutForIsolatedTest(['params']);
        $sut->expects($this->any())->method('params')->will($this->returnValue($params));

        $this->assertEquals($default, $sut->getQueryOrRouteParam($name, $default));
    }

    /**
     * Tests the Index Action. I am confident that the abtsract methds
     * this method relies on are tested in their own right. So for
     * the purpose of this test, I am only concerned with this method.
     */
    public function testIndexAction()
    {
        $id = 1;

        $view = $this->getMock('\Zend\View\View', ['setTemplate']);
        $view->expects($this->once())
             ->method('setTemplate')
             ->with('crud/index')
             ->will($this->returnSelf());

        $sut = $this->getSutForIsolatedTest(
            ['getView', 'getIdentifierName', 'checkForCrudAction', 'buildTableIntoView', 'renderView']
        );
        $sut->expects($this->once())->method('getView')
            ->will($this->returnValue($view));
        $sut->expects($this->once())->method('getIdentifierName')
            ->will($this->returnValue($id));
        $sut->expects($this->once())->method('checkForCrudAction')
            ->with(null, [], $id)->will($this->returnValue(null));
        $sut->expects($this->once())->method('buildTableIntoView');
        $sut->expects($this->once())->method('renderView')
            ->with($view)->will($this->returnValue($view));

        $this->assertSame($view, $sut->indexAction());
    }

    /**
     * Tests Get List Vars
     */
    public function testGetListVars()
    {
        $this->assertEquals([], $this->getSutForIsolatedTest(null)->getListVars());
    }

    /**
     * Build table into view adds a table into a view helper.
     */
    public function testBuildTableIntoView()
    {
        $service = 'TestingService';
        $params = [
            'one' => '1', 'two' => '2'
        ];
        $data = ['data' => 'Test Data'];

        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $sut = $this->getSutForIsolatedTest(
            array('getTableParams', 'getService', 'getDataBundle',
                'getTableName', 'makeRestCall', 'getTable', 'getViewHelperManager', 'alterTable')
        );
        $sut->expects($this->once())->method('getTableParams')->will($this->returnValue($params));
        $sut->expects($this->once())->method('getService')->will($this->returnValue($service));
        $sut->expects($this->once())->method('getDataBundle')->will($this->returnValue(['bundle']));
        $sut->expects($this->once())->method('getTableName')->will($this->returnValue('tableNameTesting'));

        $sut->expects($this->once())->method('makeRestCall')
            ->with($service, 'GET', $params, ['bundle'])
            ->will($this->returnValue($data));

        $sut->expects($this->once())->method('getViewHelperManager')->will($this->returnValue($mockViewHelperManager));

        $sut->expects($this->once())->method('getTable')
            ->with('tableNameTesting', $data, $params)
            ->will($this->returnValue('populatedTable'));

        $sut->expects($this->once())->method('alterTable')
            ->with('populatedTable')
            ->will($this->returnValue('alteredTable'));

        $this->assertEquals(null, $sut->buildTableIntoView());

        $this->assertEquals(
            'alteredTable',
            $mockViewHelperManager->get('placeholder')->getContainer('table')->getValue()
        );
    }

    /**
     * Tests the details action method.
     */
    public function testSetPlaceholder()
    {
        $namespace = 'CR TestingNamespace';
        $value = 'CR Testing Value';

        $sut = $this->getSutForIsolatedTest(['getViewHelperManager']);

        $placeholder = new \Zend\View\Helper\Placeholder();
        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);
        $sut->expects($this->once())->method('getViewHelperManager')
            ->will($this->returnValue($mockViewHelperManager));

        $this->assertEquals(null, $sut->setPlaceholder($namespace, $value));

        $this->assertEquals(
            $value,
            $mockViewHelperManager->get('placeholder')->getContainer($namespace)->getValue()
        );
    }

    /**
     * Unit test for getTableParams.
     */
    public function testGetTableParams()
    {
        $case = '1';

        $listVars = array('case');

        $valueMap = array(
            'page'  => array('page', 1, '1'),
            'sort'  => array('sort', 'id', 'id'),
            'order' => array('order', 'DESC', 'ASC'),
            'limit' => array('limit', 10, '10'),
            'case'  => array('case', null, $case)
        );

        $params = array_map(
            function($element) {
                return $element[2];
            },
            $valueMap
        );

        $sut = $this->getSutForIsolatedTest(
            array('getQueryOrRouteParam', 'getListVars', 'initTable')
        );
        $sut->expects($this->any())->method('getQueryOrRouteParam')
            ->will($this->returnValueMap($valueMap));

        $sut->expects($this->any())->method('getListVars')
            ->will($this->returnValue($listVars));

        $this->assertEquals($params, $sut->getTableParams());
    }

    /**
     * Isolatied test for the add action method calls the saveThis method.
     */
    public function testAddEditAction()
    {
        $sut = $this->getSutForIsolatedTest(['saveThis']);
        $sut->expects($this->exactly(2))
            ->method('saveThis')
            ->will($this->returnValue('saveThis'));

        $this->assertEquals('saveThis', $sut->addAction());
        $this->assertEquals('saveThis', $sut->editAction());
    }

    /**
     * Isolated test for the redirect action method.
     */
    public function redirectAction()
    {
        $redirect = $this->getMock('stdClass', 'toRoute');
        $redirect->expects($this->once())
                 ->method('toRoute')
                 ->will($this->returnValue('toRoute'));

        $sut = $this->getSutForIsolatedTest(['redirect']);
        $sut->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->assertEquals('toRoute', $sut->redirectAction());
    }

    /**
     * Isolated test for replaceIds method.
     */
    public function testReplaceIds()
    {
        $sut = $this->getSutForIsolatedTest(null);

        $idsToConvert = ['case'];

        $data = [
            'case' => [
                'id' => '1',
                'name' => 'hello'
            ],
            'licence' => [
                'id' => '2',
                'name' => 'hello licence'
            ],
            'id' => '2',
            'name' => 'top'
        ];

        $expected = array(
            'case' => '1',
            'licence' => [
                'id' => '2',
                'name' => 'hello licence'
            ],
            'id' => '2',
            'name' => 'top'
        );

        $this->assertEquals($expected, $sut->replaceIds($data, $idsToConvert));
    }

    /**
     * Isolated test for Set Navigation to Current Location functionality.
     */
    public function testSetNavigationCurrentLocation()
    {
        $page = new \Zend\Navigation\Page\Uri();
        $page->setUri('/url-test');
        $page->setId('nav-id-test');

        $sut = $this->getSutForIsolatedTest(null);

        $nav = new \Zend\Navigation\Navigation();
        $nav->addPage($page);

        $sl = new \Zend\ServiceManager\ServiceManager();
        $sl->setService('Navigation', $nav);

        $sut->setServiceLocator($sl);

        $sut->setNavigationId('nav-id-test');

        $this->assertTrue($sut->setNavigationCurrentLocation());

        $this->assertEquals(
            'nav-id-test',
            $sut->getServiceLocator()->get('Navigation')->findOneBy('active', 1)->getId()
        );
    }

    /**
     * Get a new SUT. Also tests that all the required abstracts
     * traits and interfaces are present.
     *
     * @param array $methods
     * @throws \Exception
     * @return \Olcs\Controller\CrudAbstract
     */
    public function getSutForIsolatedTest(array $methods = null)
    {
        $sut = $this->getMock($this->testClass, $methods);

        if (false === ($sut instanceof \Common\Controller\AbstractSectionController)) {
            throw new \Exception('This system under test does not extend for the correct ultimate abstract');
        }

        if (false === ($sut instanceof \Common\Controller\CrudInterface)) {
            throw new \Exception('This system under test does not implement the correct interface');
        }

        if (count(array_diff($this->traitsRequired, self::classUsesDeep($sut))) > 0) {
            throw new \Exception('This system under test does not use the correct traits');
        }

        return $sut;
    }

    /**
     * Handy method for finding all implemented traits.
     *
     * @param unknown $class
     * @param string $autoload
     * @return multitype:
     */
    public static function classUsesDeep($class, $autoload = true)
    {
        $traits = [];

        // Get traits of all parent classes
        do {
            $traits = array_merge(class_uses($class, $autoload), $traits);
        } while ($class = get_parent_class($class));

        // Get traits of all parent traits
        $traitsToSearch = $traits;
        while (!empty($traitsToSearch)) {
            $newTraits = class_uses(array_pop($traitsToSearch), $autoload);
            $traits = array_merge($newTraits, $traits);
            $traitsToSearch = array_merge($newTraits, $traitsToSearch);
        };

        foreach ($traits as $trait => $same) {
            $traits = array_merge(class_uses($trait, $autoload), $traits);
        }

        return array_unique($traits);
    }
}
