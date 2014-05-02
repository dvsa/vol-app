<?php
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Tests the Case Controller
 */
class CaseControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseController',
            [
                'getView',
                'fromRoute',
                'getTabInformationArray',
                'getCase',
                'getCaseSummaryArray',
                'getCaseDetailsArray',
                'log',
                'makeRestCall',
                'getPluginManager'
            ]
        );

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setVariables',
                'setTemplate'
            ]
        );

        $this->pm = $this->getMock('\stdClass', array('get'));

        parent::setUp();
    }

    public function testManageAction()
    {
        $caseId = '24';
        $licence = '7';
        $actionTab = 'overview';
        $tabInfo = ['overview' => [], 'convictions' => []];
        $case = ['key' => 'case'];
        $summary = ['key' => 'summary'];
        $details = ['key' => 'details'];

        $this->getFromRoute(0, 'case', $caseId);
        $this->getFromRoute(1, 'licence', $licence);
        $this->getFromRoute(2, 'tab', $actionTab);

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->controller->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));

        $this->controller->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('tab'))
            ->will($this->returnValue($actionTab));

        $this->controller->expects($this->once())
            ->method('getTabInformationArray')
            ->will($this->returnValue($tabInfo));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue($case));

        $this->controller->expects($this->once())
            ->method('getCaseSummaryArray')
            ->with($this->equalTo($case))
            ->will($this->returnValue($summary));

        $this->controller->expects($this->once())
            ->method('getCaseDetailsArray')
            ->with($this->equalTo($case))
            ->will($this->returnValue($details));

        $this->view->expects($this->once())
            ->method('setVariables')
            ->with($this->equalTo(['case' => $case, 'tabs' => $tabInfo, 'tab' => $actionTab, 'summary' => $summary, 'details' => $details]));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('case/manage'));

        $this->assertSame($this->view, $this->controller->manageAction());
    }

    /**
     * Tests the index action
     */
    public function testIndexAction()
    {

    }

    /**
     * Tests getTabInformationArray
     */
    public function testGetTabInformationArray()
    {
        $pluginMock = $this->getMock(
            'stdClass',
            [
                'get'
            ]
        );

        $pm->expects->($this->any())
            ->method('get')
            ->with('url')
            ->will($this->returnValue($))

        $this->controller->expects($this->once())
            ->method('getPluginManager')
            ->will($this->returnValue($pluginMock));

        $this->controller->getTabInformationArray();
    }

    /**
     * Tests the get case function
     *
     *
     */
    public function testGetCase()
    {
        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array('data' => 'data')));

        $this->controller->getCase(24);
    }

    public function testFromRoute()
    {
        $request = 'hello';
        $return = 'helloRet';

        $params = $this->getMock('stdClass', ['fromRoute']);
        $params->expects($this->once())
               ->method('fromRoute')
               ->with($this->equalTo($request))
               ->will($this->returnValue($return));

        $sut = $this->getMock('\Olcs\Controller\CaseController', ['params']);
        $sut->expects($this->once())
               ->method('params')
               ->will($this->returnValue($params));

        $this->assertSame($return, $sut->fromRoute($request));
    }

    public function testGetView()
    {
        $sut = new \Olcs\Controller\CaseController();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $sut->getView());
    }

    /**
     * Generate a fromRoute function call
     *
     * @param int $at
     * @param mixed $with
     * @param mixed $will
     */
    private function getFromRoute($at, $with, $will = false)
    {
        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('fromRoute')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('fromRoute')
                ->with($this->equalTo($with));
        }
    }
}
