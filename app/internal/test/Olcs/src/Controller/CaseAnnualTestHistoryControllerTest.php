<?php

/**
 * Case Prohibition Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Olcs\Controller\CaseAnnualTestHistoryController;

/**
 * Case Prohibition Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseAnnualTestHistoryControllerTest extends \PHPUnit_Framework_TestCase
{
    public function testSaveAnnualTestHistoryForm()
    {
        $data = array(
            'submit' => '',
            'id' => 42,
            'datum' => 'foo'
        );

        $cancel = array(
            'submit' => null,
            'cancel' => '',
            'id' => 42,
            'datum' => 'foo'
        );

        $sut = $this->getMock(
            '\Olcs\Controller\CaseAnnualTestHistoryController',
            array(
                'processEdit',
            )
        );

        $mockRedirect = $this->getMock('stdClass', array('toRoute'));
        $mockRedirect->expects($this->exactly(2))->method('toRoute')
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

        $sut->expects($this->once())->method('processEdit')->with($this->equalTo($data), $this->equalTo('VosaCase'));

        $sut->saveAnnualTestHistoryForm($data);
        $sut->saveAnnualTestHistoryForm($cancel);
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
        $mockParam->expects($this->exactly(2))->method('fromRoute')->will($this->returnValueMap(
                array(
                    array('case', null, 25),
                    array('licence', null, 7),
                )
            ));

        $mockPluginManager = $this->getMock('Zend\Mvc\Controller\PluginManager', array('setController', 'get'));
        $mockPluginManager->expects($this->any())->method('get')->with('params')->will($this->returnValue($mockParam));

        $sut->setPluginManager($mockPluginManager);

        $mockCase = array(
            'annualTestHistory'=> 'testing',
            'id' => 7,
            'version' => 2
        );

        $mockForm = $this->getMock('stdClass', array('setData'));
        $mockForm->expects($this->once())->method('setData')->with($this->equalTo($mockCase));

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



    /*public function setUp()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseProhibitionController',
            array(
                'getTabInformationArray',
                'getCase',
                'getCaseSummaryArray',
                'makeRestCall',
                'generateForm',
                'fromRoute',
                'setBreadcrumb',
                'processEdit',
                'processAdd',
                'redirect',
                'getView'
            )
        );

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setVariables',
                'setTemplate'
            ]
        );

        parent::setUp();
    }

    /**
     * @dataProvider indexActionProvider
     *
     * @param int $caseId
     * @param int $licenceId
     * @param array $results
     *
     * /
    public function testIndex_Action($caseId, $licenceId, $results)
    {
        $this->getFromRoute(0, 'case', $caseId);
        $this->getFromRoute(1, 'licence', $licenceId);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('getTabInformationArray');

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('getCaseSummaryArray')
            ->with($this->equalTo(array()));

        $this->controller->expects($this->once())
            ->method('makeRestCall')->
            will($this->returnValue($results));

        $form = $this->getMock('stdClass', ['setData']);
        $form->expects($this->once())
             ->method('setData');

        $this->controller->expects($this->once())
            ->method('generateForm')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setVariables');

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('case/manage');

        $this->assertEquals($this->view, $this->controller->indexAction());
    }

    /**
     * Data provider for testIndexAction
     *
     * @return array
     * /
    public function indexActionProvider()
    {
        return array(
            array(24,7, array(
                    'Count' => 1,
                    'Results' => array(
                        0 => array(
                            'case' => array(
                                'id' => 24
                            )
                        )
                    )
                )
            ),
            array(24,7,array('Count' => 0))
        );
    }

    /**
     * Tests saveProhibitionForm add is called
     *
     * @dataProvider saveProhibitionFormAddSubmitProvider
     *
     * @param array $data
     * /
    public function testSaveProhibitionFormAddSubmit($data)
    {
        $redirect = $this->getSaveRedirect();

        $this->controller->expects($this->once())
            ->method('processAdd');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->saveProhibitionForm($data);
    }

    /**
     * Tests saveProhibitionForm add is called
     *
     * @dataProvider saveProhibitionFormAddCancelProvider
     *
     * @param array $data
     * /
    public function testSaveProhibitionFormAddCancel($data)
    {
        $redirect = $this->getSaveRedirect();

        $this->controller->expects($this->never())
            ->method('processAdd');

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->saveProhibitionForm($data);
    }

    /**
     * Tests saveProhibitionForm edit is called when submit pressed
     *
     * @dataProvider saveProhibitionFormEditSubmitProvider
     *
     * @param array $data
     * /
    public function testSaveProhibitionFormEditSubmit($data)
    {
        $redirect = $this->getSaveRedirect();

        $this->controller->expects($this->once())
            ->method('processEdit');

         $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

         $this->controller->saveProhibitionForm($data);
    }

    /**
     * Tests saveProhibitionForm edit is not called when cancel pressed
     *
     * @dataProvider saveProhibitionFormEditCancelProvider
     *
     * @param array $data
     * /
    public function testSaveProhibitionFormEditCancel($data)
    {
        $redirect = $this->getSaveRedirect();

        $this->controller->expects($this->never())
            ->method('processEdit');

         $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

         $this->controller->saveProhibitionForm($data);
    }

    /**
     *
     * data provider for testSaveProhibitionForm
     *
     * @return array
     * /
    public function saveProhibitionFormAddSubmitProvider()
    {
        return array(
            array(
                array(
                    'case' => 24,
                    'notes' => 'test',
                    'submit' => '',
                    'cancel' => null
                )
            )
        );
    }

    /**
     *
     * data provider for testSaveProhibitionForm
     *
     * @return array
     * /
    public function saveProhibitionFormAddCancelProvider()
    {
        return array(
            array(
            array(
                    'case' => 24,
                    'notes' => 'test',
                    'submit' => null,
                    'cancel' => ''
            )
                )
        );
    }

    /**
     *
     * data provider for testSaveProhibitionSubmitForm
     *
     * @return array
     * /
    public function saveProhibitionFormEditSubmitProvider()
    {
        return array(
            array(
            array(
                    'id' => 1,
                    'case' => 24,
                    'notes' => 'test',
                    'submit' => '',
                    'cancel' => null
            )
                )
        );
    }

    /**
     *
     * data provider for testSaveProhibitionCancelForm
     *
     * @return array
     * /
    public function saveProhibitionFormEditCancelProvider()
    {
        return array(
            array(
            array(
                    'id' => 1,
                    'case' => 24,
                    'notes' => 'test',
                    'submit' => null,
                    'cancel' => ''
            )
                )
        );
    }

    /**
     * Generate a fromRoute function call
     *
     * @param int $at
     * @param mixed $with
     * @param mixed $will
     * /
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

    /**
     * Creates a mock class (used for the redirect method)
     *
     * /
    private function getSaveRedirect()
    {
        $redirect = $this->getMock('stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with($this->equalTo('case_prohibition'), $this->equalTo(array()), $this->equalTo(array()), $this->equalTo(true));

        return $redirect;
    }*/
}
