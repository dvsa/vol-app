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
                'fromPost',
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
            ->method('setBreadcrumb')
            ->with(
                $this->equalTo(
                    array('licence_case_list/pagination' => array('licence' => $licenceId))
                )
            );

        $this->controller->expects($this->once())
            ->method('getTabInformationArray');

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($this->equalTo($caseId))
            ->will($this->returnValue([]));

        $this->controller->expects($this->once())
            ->method('getCaseSummaryArray');

        $this->controller->expects($this->any())
            ->method('makeRestCall')->
            will(
                $this->onConsecutiveCalls(
                    $this->returnValue($this->getSampleProhibitionResult($resultCount)),
                    $this->returnValue($results)
                )
            );

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
     * Returns a licence ID and case ID
     *
     * @return array
     */
    public function licenceAndCaseIdProvider()
    {
        return [
            [7, 28]
        ];
    }

    /**
     * Tests the add action
     *
     * @dataProvider licenceAndCaseIdProvider
     *
     * @param int $licenceId
     * @param int $caseId
     */
    public function testAddAction($licenceId, $caseId)
    {
        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(
                $this->equalTo(
                    array(
                        'licence_case_list/pagination' => array('licence' => $licenceId),
                        'case_prohibition' => array('licence' => $licenceId, 'case' => $caseId)
                    )
                )
            );

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('prohibition/form');

        $this->assertEquals($this->view, $this->controller->addAction());
    }

    /**
     * Returns a licence ID, case ID, prohibition ID and result count
     *
     * @return array
     */
    public function editActionProvider()
    {
        return [
            [7, 28, 1, 1],
            [7, 28, 1, 0]
        ];
    }

    /**
     * Tests the edit action
     *
     * @dataProvider editActionProvider
     *
     * @param int $licenceId
     * @param int $caseId
     * @param int $prohibitionId
     * @param int $resultCount
     */
    public function testEditAction($licenceId, $caseId, $prohibitionId, $resultCount)
    {
        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromRoute(2, 'id', $prohibitionId);

        $this->getFromPost(3, 'action', null);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(
                $this->equalTo(
                    array(
                        'licence_case_list/pagination' => array('licence' => $licenceId),
                        'case_prohibition' => array('licence' => $licenceId, 'case' => $caseId)
                    )
                )
            );

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue($this->getSingleProhibitionResult()),
                    $this->returnValue(array('Count' => $resultCount))
                )
            );

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('buildTable');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('prohibition/form');

        $this->assertEquals($this->view, $this->controller->editAction());
    }

    /**
     * Tests the edit action correctly redirects to the add defect action
     */
    public function testEditActionAddDefectRedirect()
    {
        $this->getEditActionRouteParams('Add');

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition/defect'),
                $this->equalTo(['action' => 'add']),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->editAction();
    }

    /**
     * Tests the edit action correctly redirects to the edit defect action
     */
    public function testEditActionEditDefectRedirect()
    {
        $this->getEditActionRouteParams('Edit');

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition/defect'),
                $this->equalTo(['action' => 'edit', 'defect' => 1]),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->editAction();
    }

    /**
     * Tests the edit action correctly redirects to the delete defect action
     */
    public function testEditActionDeleteDefectRedirect()
    {
        $this->getEditActionRouteParams('Delete');

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition/defect'),
                $this->equalTo(['action' => 'delete', 'defect' => 1]),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->editAction();
    }

    public function getEditActionRouteParams($defectAction)
    {
        $this->getFromRoute(0, 'licence', 7);
        $this->getFromRoute(1, 'case', 28);
        $this->getFromRoute(2, 'id', 1);

        $this->getFromPost(3, 'action', $defectAction);
        $this->getFromPost(4, 'id', 1);
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
            'main' => array(
                'case' => 24,
                'notes' => 'test',
                'submit' => '',
                'cancel' => null
            )
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
                    'main' => array(
                        'id' => 1,
                        'case' => 24,
                        'notes' => 'test',
                        'main' => []
                    )
                )
            )
        );
    }

    /**
     * Generate a fromPost function call
     *
     * @param int $at
     * @param mixed $with
     * @param mixed $will
     */
    private function getFromPost($at, $with, $will = false)
    {
        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('fromPost')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('fromPost')
                ->with($this->equalTo($with));
        }
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
     * Returns a sample result of a prohibition rest call
     * Accepts a result count (used to test code path in index action)
     *
     * @param int $count
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
                    'id' => 'pro_t_si'
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
