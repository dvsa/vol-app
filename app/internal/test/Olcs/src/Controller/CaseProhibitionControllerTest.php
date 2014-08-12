<?php

/**
 * Case Prohibition Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Case Prohibition Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseProhibitionControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
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
                'getView',
                'getServiceLocator',
                'buildTable',
                'generateFormWithData',
                'redirectToIndex',
                'redirectToRoute',
                'notFoundAction'
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
     * @param int $resultCount
     *
     */
    public function testIndexAction($caseId, $licenceId, $results, $resultCount)
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

        $this->controller->expects($this->any())
            ->method('makeRestCall')->
            will(
                $this->onConsecutiveCalls(
                    $this->returnValue($this->getSampleProhibitionResult($resultCount)),
                    $this->returnValue($results)
                )
            );

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorStaticData()));

        $this->controller->expects($this->once())
            ->method('buildTable');

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
     */
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
                ),
                1
            ),
            array(24,7,array('Count' => 0),0)
        );
    }

    /**
     * Tests the add action
     */
    public function testAddAction()
    {
        $this->getFromRoute(0, 'licence', 7);
        $this->getFromRoute(1, 'case', 28);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('prohibition/form');

        $this->controller->addAction();
    }

    /**
     * Tests the edit action
     */
    public function testEditAction()
    {
        $this->getFromRoute(0, 'licence', 7);
        $this->getFromRoute(1, 'case', 28);
        $this->getFromRoute(2, 'id', 1);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will(
                $this->returnValue($this->getSingleProhibitionResult())
            );

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('prohibition/form');

        $this->controller->editAction();
    }

    /**
     * Tests the edit action when record not found
     */
    public function testEditActionNotFound()
    {
        $this->getFromRoute(0, 'licence', 7);
        $this->getFromRoute(1, 'case', 28);
        $this->getFromRoute(2, 'id', 1);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will(
                $this->returnValue([])
            );

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }

    /**
     * Tests process add prohibition success
     */
    public function testProcessAddProhibition()
    {
        $data = $this->getDataForSave();

        $this->controller->expects($this->once())
             ->method('processAdd')
             ->will($this->returnValue(['id' => 1]));

        $this->controller->expects($this->once())
             ->method('redirectToIndex');

        $this->controller->processAddProhibition($data);
    }

    /**
     * Tests process add prohibition failure
     */
    public function testProcessAddProhibitionFail()
    {
        $data = $this->getDataForSave();

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue([]));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition'),
                $this->equalTo(array('action' => 'add')),
                $this->equalTo(array()),
                $this->equalTo(true)
            );

        $this->controller->processAddProhibition($data);
    }

    /**
     * Tests process edit prohibition success
     */
    public function testProcessEditProhibition()
    {
        $data = $this->getDataForSave();

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue([]));

        $this->controller->expects($this->once())
            ->method('redirectToIndex');

        $this->controller->processEditProhibition($data);
    }

    /**
     * Tests process edit prohibition failure
     */
    public function testProcessEditProhibitionFail()
    {
        $data = $this->getDataForSave();

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(['failed' => 'failed']));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition'),
                $this->equalTo(array('action' => 'edit')),
                $this->equalTo(array()),
                $this->equalTo(true)
            );

        $this->controller->processEditProhibition($data);
    }

    /**
     * Tests saveProhibitionForm add is called
     *
     * @return void
     */
    public function testSaveProhibitionFormAddSubmit()
    {
        $data = array(
            'case' => 24,
            'notes' => 'test',
            'submit' => '',
            'cancel' => null
        );

        $this->controller->expects($this->once())
            ->method('processAdd');

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition'),
                $this->equalTo(array()),
                $this->equalTo(array()),
                $this->equalTo(true)
            );

        $this->controller->saveProhibitionNoteForm($data);
    }

    /**
     * Tests saveProhibitionForm edit is called when submit pressed
     *
     * @dataProvider saveProhibitionFormEditSubmitProvider
     *
     * @param array $data
     */
    public function testSaveProhibitionFormEditSubmit($data)
    {
        $this->controller->expects($this->once())
            ->method('processEdit');

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition'),
                $this->equalTo(array()),
                $this->equalTo(array()),
                $this->equalTo(true)
            );

        $this->controller->saveProhibitionNoteForm($data);
    }

    /**
     * Tests getDeleteServiceName
     */
    public function testGetDeleteServiceName()
    {
        $this->assertEquals('Prohibition', $this->controller->getDeleteServiceName());
    }

    /**
     *
     * data provider for testSaveProhibitionSubmitForm
     *
     * @return array
     */
    public function saveProhibitionFormEditSubmitProvider()
    {
        return array(
            array(
            array(
                    'id' => 1,
                    'case' => 24,
                    'notes' => 'test',
                    'main' => [],
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

    /**
     * Gets data for a save
     *
     * @return array
     */
    private function getDataForSave()
    {
        return [
            'fields' => [],
            'id' => 1,
            'case_id' => 28,
            'version' => 1
        ];
    }

    /**
     * Sample static data
     *
     * @return array
     */
    private function getSampleStaticData()
    {
        return array(
            'prohibition_type' => [
                'prohibition_type.1' => 'Immediate (S)',
                'prohibition_type.2' => 'Delayed (S)',
                'prohibition_type.3' => 'Variation (S)',
                'prohibition_type.4' => 'Immediate',
                'prohibition_type.5' => 'Delayed',
                'prohibition_type.6' => 'Variation',
                'prohibition_type.7' => 'Refusals Only',
                'prohibition_type.8' => 'Variation & Refusals Only',
            ]
        );
    }

    /**
     * Gets a mock version of static-list-data
     */
    private function getServiceLocatorStaticData ()
    {
        $serviceMock = $this->getMock('\stdClass', array('get'));

        $scriptMock = $this->getMock('\stdClass', ['loadFiles']);
        $scriptMock->expects($this->any())
            ->method('loadFiles')
            ->will($this->returnValue([]));

        $serviceMock->expects($this->any())
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    array('static-list-data' => $this->getSampleStaticData()),
                    $scriptMock
                )
            );

        return $serviceMock;
    }

    /**
     * Returns a sample result of a prohibition rest call
     * Accepts a result count (used to test code path in index action)
     *
     * @param $count
     * @return array
     */
    private function getSampleProhibitionResult($count)
    {
        return [
            'Count' => $count,
            'Results' => [
                'id' => 1,
                'prohibitionDate' => '2014-01-01',
                'clearedDate' => '2014-01-02',
                'isTrailer' => 'N',
                'vrm' => 'AB62 CDE',
                'imposedAt' => 'Doncaster',
                'version' => 1,
                'prohibitionType' => [
                    'handle' => 'prohibition_type.1'
                ],
                'case' => [
                    'id' => 1
                ]
            ]
        ];
    }

    /**
     * Gets a single result of a prohibition rest call
     * 
     * @return array
     */
    private function getSingleProhibitionResult()
    {
        $result = $this->getSampleProhibitionResult(1);

        return $result['Results'];
    }
}
