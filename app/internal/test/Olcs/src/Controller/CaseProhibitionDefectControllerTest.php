<?php

/**
 * Case Prohibition Defect Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Case Prohibition Defect Controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseProhibitionDefectControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseProhibitionDefectController',
            array(
                'setBreadcrumb',
                'generateFormWithData',
                'getView',
                'fromRoute',
                'makeRestCall',
                'processEdit',
                'processAdd',
                'notFoundAction',
                'redirectToRoute'
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
     * Adds an index redirect to the test
     */
    public function addRedirectToIndex()
    {
        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition'),
                $this->equalTo(['action' => 'edit']),
                $this->equalTo([]),
                $this->equalTo(true)
            );
    }

    /**
     * Adds a failure redirect to the test
     */
    public function addRedirectOnFailure()
    {
        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo('case_prohibition/defect'),
                $this->equalTo([]),
                $this->equalTo([]),
                $this->equalTo(true)
            );
    }

    /**
     * Data provider for testAddAction
     *
     * @return array
     */
    public function addActionDataProvider()
    {
        return [
            [7, 24, 1]
        ];
    }

    /**
     * Tests the add action
     *
     * @dataProvider addActionDataProvider
     *
     * @param int $licenceId
     * @param int $caseId
     * @param int $prohibitionId
     */
    public function testAddAction($licenceId, $caseId, $prohibitionId)
    {
        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromRoute(2, 'id', $prohibitionId);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
             ->method('getView')
             ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('prohibition/defect');

        $this->controller->addAction();
    }

    /**
     * Data provider for testEditAction
     *
     * @return array
     */
    public function editActionProvider()
    {
        return [
            [7, 24, 1, 2]
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
     * @param int $defectId
     */
    public function testEditAction($licenceId, $caseId, $prohibitionId, $defectId)
    {
        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromRoute(2, 'id', $prohibitionId);
        $this->getFromRoute(3, 'defect', $defectId);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
             ->method('makeRestCall')
             ->will($this->returnValue($this->getSampleProhibitionDefect()));

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('prohibition/defect');

        $this->controller->editAction();
    }

    /**
     * Tests the edit action correctly returns a notFound action
     *
     * @dataProvider editActionProvider
     *
     * @param int $licenceId
     * @param int $caseId
     * @param int $prohibitionId
     * @param int $defectId
     */
    public function testEditActionNotFound($licenceId, $caseId, $prohibitionId, $defectId)
    {
        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromRoute(2, 'id', $prohibitionId);
        $this->getFromRoute(3, 'defect', $defectId);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue([]));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }

    /**
     * Tests processing of add form
     */
    public function testProcessAddProhibitionDefect()
    {
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(['id' => 1]));

        $this->addRedirectToIndex();

        $this->controller->processAddProhibitionDefect($this->getSampleProhibitionDefectPost());
    }

    /**
     * Tests the add form redirects correctly on failure
     */
    public function testProcessAddProhibitionDefectFails()
    {
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(['fail' => 'failed']));

        $this->addRedirectOnFailure();

        $this->controller->processAddProhibitionDefect($this->getSampleProhibitionDefectPost());
    }

    /**
     * Tests the edit form processing
     */
    public function testProcessEditProhibitionDefect()
    {
        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue([]));

        $this->addRedirectToIndex();

        $this->controller->processEditProhibitionDefect($this->getSampleProhibitionDefectPost());
    }

    /**
     * Tests the edit form redirects correctly on failure
     */
    public function testProcessEditProhibitionDefectFails()
    {
        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(['fail' => 'fail']));

        $this->addRedirectOnFailure();

        $this->controller->processEditProhibitionDefect($this->getSampleProhibitionDefectPost());
    }

    /**
     * Tests getDeleteServiceName
     */
    public function testGetDeleteServiceName()
    {
        $this->assertEquals('ProhibitionDefect', $this->controller->getDeleteServiceName());
    }

    /**
     * Sample of prohibition defect form post
     *
     * @return array
     */
    public function getSampleProhibitionDefectPost()
    {
        return [
            'main' => [
                'defectType' => 'defect type',
                'notes' => 'notes'
            ],
            'id' => 1,
            'prohibition' => 1,
            'version' => 1
        ];
    }

    /**
     * Returns a sample prohibition defect
     *
     * @return array
     */
    public function getSampleProhibitionDefect()
    {
        return [
            'defectType' => 'defect type',
            'notes' => 'notes',
            'id' => 1,
            'prohibition' => 1,
            'version' => 1
        ];
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
