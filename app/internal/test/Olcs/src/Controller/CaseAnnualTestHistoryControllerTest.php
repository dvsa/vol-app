<?php

/**
 * Case Prohibition Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

/**
 * Case Prohibition Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseAnnualTestHistoryControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveAnnualTestHistoryForm()
    {
        $dataOne = array(
            'main' => array(
                'id' => 42,
                'datum' => 'foo'
            )
        );

        $dataTwo = $dataOne + $dataOne['main'];
        unset($dataTwo['main']);

        $sut = $this->getMock(
            '\Olcs\Controller\CaseAnnualTestHistoryController',
            array(
                'processEdit',
            )
        );

        $mockRedirect = $this->getMock('stdClass', array('toRoute'));
        $mockRedirect->expects($this->exactly(1))->method('toRoute')
            ->with(
                $this->equalTo('case_annual_test_history'),
                $this->equalTo(array()),
                $this->equalTo(array()),
                $this->equalTo(true)
            );

        $mockPluginManager = $this->getMock('Zend\Mvc\Controller\PluginManager', array('setController', 'get'));
        $mockPluginManager->expects($this->any())->method('get')->with('redirect')
            ->will($this->returnValue($mockRedirect));

        $sut->setPluginManager($mockPluginManager);

        $sut->expects($this->once())->method('processEdit')->with($this->equalTo($dataTwo), $this->equalTo('VosaCase'));

        $sut->saveAnnualTestHistoryForm($dataOne);
    }

    public function testIndexAction()
    {
        $sut = $this->getMock(
            '\Olcs\Controller\CaseAnnualTestHistoryController',
            array(
                'getTabInformationArray',
                'getCase',
                'getCaseSummaryArray',
                //'makeRestCall',
                //'generateForm',
                'setBreadcrumb',
                //'processEdit',
                //'processAdd',
                //'redirect',
                'generateForm'
            )
        );

        $mockParam = $this->getMock('stdClass', array('fromRoute'));
        $mockParam->expects($this->exactly(2))->method('fromRoute')->will(
            $this->returnValueMap(
                array(
                    array('case', null, 25),
                    array('licence', null, 7),
                )
            )
        );

        $mockPluginManager = $this->getMock('Zend\Mvc\Controller\PluginManager', array('setController', 'get'));
        $mockPluginManager->expects($this->any())->method('get')->with('params')->will($this->returnValue($mockParam));

        $sut->setPluginManager($mockPluginManager);

        $mockCase = array(
            'annualTestHistory'=> 'testing',
            'id' => 7,
            'version' => 2
        );

        $formData = [];
        $formData['main'] = $mockCase;
        $mockForm = $this->getMock('stdClass', array('setData'));
        $mockForm->expects($this->once())->method('setData')->with($this->equalTo($formData));

        $sut->expects($this->once())->method('getCase')->with($this->equalTo(25))
            ->will($this->returnValue($mockCase));
        $sut->expects($this->once())->method('generateForm')
            ->with($this->equalTo('annual-test-history-comment'), $this->equalTo('saveAnnualTestHistoryForm'))
            ->will($this->returnValue($mockForm));
        $sut->expects($this->once())->method('getTabInformationArray')->will($this->returnValue('tabs'));
        $sut->expects($this->once())->method('getCaseSummaryArray')->with($this->equalTo($mockCase))
            ->will($this->returnValue('summary'));

        $sut->expects($this->once())->method('setBreadcrumb')
            ->with($this->equalTo(array('licence_case_list/pagination' => array('licence' => 7))));

        $view = $sut->indexAction();

        $expected = array(
            'case' => $mockCase,
            'tabs' => 'tabs',
            'tab' => 'annual_test_history',
            'summary' => 'summary',
            'commentForm' => $mockForm
        );
        $this->assertEquals($expected, $view->getVariables()->getArrayCopy());
    }
}
