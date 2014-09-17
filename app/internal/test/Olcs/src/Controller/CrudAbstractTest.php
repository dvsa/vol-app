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
     * Get a new SUT. Also tests that all the required abstracts
     * traits and interfaces are present.
     *
     * @param array $methods
     * @throws \Exception
     * @return \Olcs\Controller\CrudAbstract
     */
    public function getSutForIsolatedTest(array $methods = [])
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