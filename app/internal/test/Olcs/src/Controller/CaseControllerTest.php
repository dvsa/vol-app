<?php
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class IndexControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );
        parent::setUp();
    }

    public function manageAction()
    {
        $caseId = '12345';
        $actionTab = 'overview';
        $tabInfo = ['overview' => [], 'convictions' => []];
        $case = ['key' => 'case'];
        $summary = ['key' => 'summary'];
        $details = ['key' => 'details'];

        $sut = $this->getMock(
            '\Olcs\Controller\CaseController',
            [
                'getView',
                'fromRoute',
                'getTabInformationArray',
                'getCase',
                'getCaseSummaryArray',
                'getCaseDetailsArray',
                'log'
            ]
        );

        $view = $this->getMock(
            'Zend\View\Model\ViewModel',
            ['setVariables', 'setTemplate']
        );

        $sut->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($view));

        $sut->expects($this->at(1))
            ->method('fromRoute')
            ->with($this->equalTo('case'))
            ->will($this->returnValue($caseId));

        $sut->expects($this->at(2))
            ->method('fromRoute')
            ->with($this->equalTo('tab'))
            ->will($this->returnValue($actionTab));

        $sut->expects($this->once())
            ->method('getTabInformationArray')
            ->will($this->returnValue($tabInfo));

        $sut->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue($case));

        $sut->expects($this->once())
            ->method('getCaseSummaryArray')
            ->with($this->equalTo($case))
            ->will($this->returnValue($summary));

        $sut->expects($this->once())
            ->method('getCaseDetailsArray')
            ->with($this->equalTo($case))
            ->will($this->returnValue($details));

        $view->expects($this->once())
            ->method('setVariables')
            ->with($this->equalTo(['case' => $case, 'tabs' => $tabInfo, 'tab' => $actionTab, 'summary' => $summary, 'details' => $details]));

        $view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('case/manage'));

        $this->assertSame($view, $sut->manageAction());
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
}