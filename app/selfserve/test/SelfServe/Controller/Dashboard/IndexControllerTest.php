<?php

namespace OlcsTest\Controller\Dashboard;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;

class IndexControllerTest extends AbstractHttpControllerTestCase
{

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\Dashboard\IndexController',
            $methods
        );

        $this->controller->setServiceLocator($this->getApplicationServiceLocator());

    }

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        parent::setUp();

    }

    /**
     * @group Dashboard
     *
     */
    public function testIndexAction()
    {
        $this->setUpMockController([
            'makeRestCall',
            'getPluginManager',
            'redirect',
            'params',
        ]);

        $urlPlugin = $this->getMock('\stdClass', array('fromRoute'));

        $mockPluginManager = $this->getMock('\stdClass', array('get'));
        $mockPluginManager->expects($this->any())
            ->method('get')
            ->will($this->returnValue($urlPlugin));

        $this->controller->expects($this->any())
            ->method('getPluginManager')
            ->will($this->returnValue($mockPluginManager));


        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->restCallMap()))
        ;

        $redirectMock = $this->getMock('\stdClass', array('toRoute'));
        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($redirectMock))
        ;

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('userId')
            ->will($this->returnValue(1));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->indexAction();
    }

    /**
     * @group Dashboard
     *
     */
    public function testIndexActionWithNoUser()
    {
        $this->setUpMockController([
            'makeRestCall',
            'getPluginManager',
            'redirect',
            'params',
        ]);

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->restCallMap()))
        ;

        $redirectMock = $this->getMock('\stdClass', array('toRoute'));
        $redirectMock->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue(new \Zend\Http\Response));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($redirectMock))
        ;

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('userId')
            ->will($this->returnValue(null));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->indexAction();
    }

    /**
     * @group Dashboard
     * @group current
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testIndexActionWithUserNotFound()
    {
        $this->setUpMockController([
            'makeRestCall',
            'getPluginManager',
            'redirect',
            'params',
        ]);

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->restCallMap(2, 1, 1)))
        ;

        $redirectMock = $this->getMock('\stdClass', array('toRoute'));
        $redirectMock->expects($this->any())
            ->method('toRoute')
            ->will($this->returnValue(new \Zend\Http\Response));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($redirectMock))
        ;

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));

        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('userId')
            ->will($this->returnValue(2));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->indexAction();
    }


    /**
     * @group Dashboard
     *
     */
    public function testCreateApplicationAction()
    {
        $this->setUpMockController([
            'makeRestCall',
            'redirect',
            'params',
        ]);

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->restCallMap()))
        ;

        $redirectMock = $this->getMock('\stdClass', array('toRoute'));
        $redirectMock->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue(new \Zend\Http\Response));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirectMock))
        ;



        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('userId')
            ->will($this->returnValue(1));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->createApplicationAction();
    }

    /**
     * @group Dashboard
     *
     */
    public function testCreateApplicationActionWithNoUser()
    {
        $this->setUpMockController([
            'makeRestCall',
            'redirect',
            'params',
        ]);

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->restCallMap()))
        ;

        $redirectMock = $this->getMock('\stdClass', array('toRoute'));
        $redirectMock->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue(new \Zend\Http\Response));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirectMock))
        ;



        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('userId')
            ->will($this->returnValue(null));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->createApplicationAction();
    }

    /**
     *
     * @group Dashboard
     */
    public function testDetermineSectionAction()
    {
        $this->setUpMockController([
            'makeRestCall',
            'redirect',
            'params',
        ]);

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue(1));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->restCallMap()))
        ;

        $redirectMock = $this->getMock('\stdClass', array('toRoute'));
        $redirectMock->expects($this->once())
            ->method('toRoute')
            ->will($this->returnValue(new \Zend\Http\Response));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirectMock))
        ;

        $this->controller->determineSectionAction();
    }

    /**
     *
     * @group Dashboard
     * @expectedException \Common\Exception\ResourceNotFoundException
     */
    public function testDetermineSectionActionWithNoResults()
    {
        $this->setUpMockController([
            'makeRestCall',
            'redirect',
            'params',
        ]);

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with('applicationId')
            ->will($this->returnValue(1));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->restCallMap(1,1,0)))
        ;


        $this->controller->determineSectionAction();
    }


    private function restCallMap($userId = 1, $organisationId = 1, $applicationId = 1)
    {
        return [
            [
                'User',
                'GET',
                ['id' => $userId],
                ['children' => ['organisation']],
                $userId == 1 ? ['organisation' => ['id' => $organisationId]] : false
            ],
            [
                'OrganisationApplication',
                'GET',
                ['organisation' => $organisationId],
                ['children' => ['licence']],
                []
            ],
            [
            'ApplicationCompletion',
                'GET',
                ['application' => $applicationId],
                null,
                [
                    'Count' => $applicationId == 1 ? 1 : 0,
                    'Results' => [
                        [
                            'lastSection' => 'business-type',
                        ]
                    ]
                ]
            ]
        ];
    }


}
