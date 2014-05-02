<?php

namespace OlcsTest\View\JourneyHelper;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class JourneyHelperTest extends AbstractHttpControllerTestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\View\Helper\Journey',
            $methods
        );
    }

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../config/application.config.php'
        );

        parent::setUp();

    }

    /**
     * Test invocation on Journey View Helper
     * with completed status
     */
    public function testInvoke()
    {
        $this->setUpMockController( [
            'getView',
            'getServiceLocator',
            'renderer',
            'view',
            'url'
        ]);

        $journeyArray=Array(
            'journeyCompletionStatus' => array(
                    0 => '',
                    1 => 'incomplete',
                    2 => 'complete'
            ),
            'journey' => array(
                'teststage' => array(
                    'dbkey' => 'Tol',
                    'route' => 'licence-type',
                    'label' => 'type-of-licence',
                    'step'  => 'operator-location'
                ),
            )
        );

        $completeArray=Array(
            'sectionTolStatus' => 1
        );

        $mockView = $this->getMock('\stdClass', array('url','render'));
        $mockView->expects($this->once())
            ->method('url')
            ->will($this->returnValue("testurl"));
        $mockView->expects($this->once())
            ->method('render')
            ->will($this->returnValue("blah"));

        $this->controller->expects($this->any())
                ->method('getView')
                ->will($this->returnValue($mockView));

        $serviceLocatorMock = $this->getMock('\stdClass', array('get'));
        $serviceLocatorMock->expects($this->once())
            ->method('get')
            ->with('config')
            ->will($this->returnValue($journeyArray));

        $serviceLocatorSuperMock = $this->getMock('\stdClass', array('getServiceLocator'));
        $serviceLocatorSuperMock->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocatorMock));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocatorSuperMock));

        $this->controller->__invoke('teststage',$completeArray,1);
    }

    /**
     * Test invocation on Journey View Helper without completed status
     * with completed status
     */
    public function testInvokeWithoutComplete()
    {
        $this->setUpMockController( [
            'getView',
            'getServiceLocator',
            'renderer',
            'view',
            'url'
        ]);

        $journeyArray=Array(
            'journeyCompletionStatus' => array(
                    0 => '',
                    1 => 'incomplete',
                    2 => 'complete'
            ),
            'journey' => array(
                'teststage' => array(
                    'dbkey' => 'Tol',
                    'route' => 'licence-type',
                    'label' => 'type-of-licence',
                    'step'  => 'operator-location'
                ),
            )
        );

        $completeArray=Array(
            'sectionTolStatus' => null
        );

        $mockView = $this->getMock('\stdClass', array('url','render'));
        $mockView->expects($this->once())
            ->method('url')
            ->will($this->returnValue("testurl"));
        $mockView->expects($this->once())
            ->method('render')
            ->will($this->returnValue("blah"));

        $this->controller->expects($this->any())
                ->method('getView')
                ->will($this->returnValue($mockView));

        $serviceLocatorMock = $this->getMock('\stdClass', array('get'));
        $serviceLocatorMock->expects($this->once())
            ->method('get')
            ->with('config')
            ->will($this->returnValue($journeyArray));

        $serviceLocatorSuperMock = $this->getMock('\stdClass', array('getServiceLocator'));
        $serviceLocatorSuperMock->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocatorMock));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocatorSuperMock));

        $this->controller->__invoke('teststage',$completeArray,1);
    }

}