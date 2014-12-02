<?php
/**
 * Case note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Mockery as m;

/**
 * Case note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class NoteControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Cases\Processing\NoteController',
            array(
                'makeRestCall',
                'getLoggedInUser',
                'getTable',
                'generateFormWithData',
                'getFromRoute',
                'getFromPost',
                'getForm',
                'getView',
                'url',
                'renderView',
                'redirectToRoute',
                'setTableFilters',
                'loadScripts',
                'getCase',
                'getRequest'

            )
        );

        $this->view = $this->getMock(
            '\Zend\View\Model\ViewModel',
            array(
                'setTemplate',
            )
        );

        $this->form = $this->getMock(
            '\Zend\Form\Form',
            array(
                'remove',
                'setData'
            )
        );

        parent::setUp();
    }

    /**
     * Tests index action when there is a licence in the route
     */
    public function testIndexAction()
    {
        $licenceId = 7;
        $caseId = 28;

        $requestArray = array(
            'page' => 1,
            'sort' => 'priority',
            'order' => 'DESC',
            'limit' => 10,
            'noteType' => 'note_t_lic'
        );

        $table = $this->getMock(
            'Common\Service\Table\TableBuilder', [], [], '', false
        );

        $mockParams = m::mock('\Zend\Stdlib\Parameters');
        $mockParams->shouldReceive('toArray')->andReturn($requestArray);

        $mockRequest = m::mock('\Zend\Http\PhpEnvironment\Request');
        $mockRequest->shouldReceive('getQuery')->andReturn($mockParams);

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromPost(2, 'action', null);
        $this->getFromPost(3, 'id', null);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($this->getSampleResult()));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->with('note-filter')
            ->will($this->returnValue($this->form));

        $this->form->expects($this->once())
            ->method('remove')
            ->with('csrf');

        $this->form->expects($this->once())
            ->method('setData');

        $this->controller->expects($this->once())
            ->method('setTableFilters')
            ->with($this->form);

        $this->controller->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue($table));

        $this->controller->expects($this->once())
            ->method('loadScripts')
            ->with(['forms/filter','table-actions']);

        $this->controller->expects($this->once())
            ->method('getView')
            ->with($this->equalTo(['table' => $table]))
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->controller->getTemplatePrefix() . '/notes/index');

        $this->controller->expects($this->once())
            ->method('renderView');

        $this->controller->indexAction();
    }

    /**
     * Tests index action when there is no licence in the route
     *
     * @dataProvider indexActionNoRouteLicenceProvider
     *
     * @param $caseId
     * @param $caseLicenceId
     */
    public function testIndexActionNoRouteLicence($caseId, $caseLicenceId)
    {
        $licenceId = null;

        $requestArray = array(
            'page' => 1,
            'sort' => 'priority',
            'order' => 'DESC',
            'limit' => 10,
            'noteType' => 'note_t_lic'
        );

        $table = $this->getMock(
            'Common\Service\Table\TableBuilder', [], [], '', false
        );

        $mockParams = m::mock('\Zend\Stdlib\Parameters');
        $mockParams->shouldReceive('toArray')->andReturn($requestArray);

        $mockRequest = m::mock('\Zend\Http\PhpEnvironment\Request');
        $mockRequest->shouldReceive('getQuery')->andReturn($mockParams);

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($mockRequest));

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromPost(2, 'action', null);
        $this->getFromPost(3, 'id', null);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($this->getSampleResult()));

        $this->controller->expects($this->once())
            ->method('getForm')
            ->with('note-filter')
            ->will($this->returnValue($this->form));

        $this->form->expects($this->once())
            ->method('remove')
            ->with('csrf');

        $this->form->expects($this->once())
            ->method('setData');

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($caseId)
            ->will($this->returnValue(['licence' => ['id' => $caseLicenceId]]));

        $this->controller->expects($this->once())
            ->method('setTableFilters')
            ->with($this->form);

        $this->controller->expects($this->once())
            ->method('getTable')
            ->will($this->returnValue($table));

        $this->controller->expects($this->once())
            ->method('loadScripts')
            ->with(['forms/filter','table-actions']);

        $this->controller->expects($this->once())
            ->method('getView')
            ->with($this->equalTo(['table' => $table]))
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->controller->getTemplatePrefix() . '/notes/index');

        $this->controller->expects($this->once())
            ->method('renderView');

        $this->controller->indexAction();
    }

    public function indexActionNoRouteLicenceProvider()
    {
        return [
            [28, null],
            [28, 7]
        ];
    }

    /**
     * Tests for a crud add redirect from index action
     */
    public function testIndexActionAddRedirect()
    {
        $licenceId = 7;
        $caseId = 28;
        $action = 'Add';
        $id = null;
        $route = $this->controller->getRoutePrefix() . '/add-note';

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromPost(2, 'action', $action);
        $this->getFromPost(3, 'id', $id);

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo($route),
                $this->equalTo(
                    [
                        'action' => strtolower($action),
                        'licence' => $licenceId,
                        'noteType' => 'note_t_case',
                        'linkedId' => $caseId,
                        'case' => $caseId,
                        'application' => null

                    ]
                ),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->indexAction();
    }

    /**
     * Tests for a crud edit/delete redirect from index action
     *
     * @dataProvider indexActionModifyRedirectProvider
     *
     * @param string $action
     */
    public function testIndexActionModifyRedirect($action)
    {
        $licenceId = 7;
        $id = 1;
        $caseId = 28;
        $route = $this->controller->getRoutePrefix() . '/modify-note';

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromPost(2, 'action', $action);
        $this->getFromPost(3, 'id', $id);

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo($route),
                $this->equalTo(['action' => strtolower($action), 'id' => $id]),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->indexAction();
    }

    public function testAddAction()
    {
        $licenceId = 7;
        $noteType = 'note_t_case';
        $linkedId = 1;
        $caseId = 28;

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'case', $caseId);
        $this->getFromRoute(2, 'noteType', $noteType);
        $this->getFromRoute(3, 'linkedId', $linkedId);

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getCase')
            ->with($caseId)
            ->will($this->returnValue(['licence' => ['id' => $licenceId]]));

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->controller->getTemplatePrefix() . '/notes/form');

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($this->view));

        $this->controller->addAction();
    }

    public function indexActionModifyRedirectProvider()
    {
        return [
            ['Edit', 'Delete']
        ];
    }

    private function getSampleResult()
    {
        return [
            'Results' => [
                0 => [
                    'noteType' => [
                        'id' => 'note_t_case',
                        'description' => 'Case'
                    ]
                ]
            ]
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
                ->method('getFromRoute')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('getFromRoute')
                ->with($this->equalTo($with));
        }
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
                ->method('getFromPost')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('getFromPost')
                ->with($this->equalTo($with));
        }
    }
}
