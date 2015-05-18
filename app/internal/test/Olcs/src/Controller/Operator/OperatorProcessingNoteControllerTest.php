<?php
/**
 * Operator Processing Note controller tests
 */
namespace OlcsTest\Controller\Operator;

use OlcsTest\Controller\ProcessingNoteControllerTestAbstract;

/**
 * Operator Processing Note controller tests
 */
class OperatorProcessingNoteControllerTest extends ProcessingNoteControllerTestAbstract
{
    protected $testClass = '\Olcs\Controller\Operator\OperatorProcessingNoteController';
    protected $mainIdRouteParam = 'organisation';

    /**
     * Tests for a crud add redirect from index action
     */
    public function testIndexActionAddRedirect()
    {
        $organisationId = 123;
        $action = 'Add';
        $id = null;
        $route = $this->controller->getRoutePrefix() . '/add-note';

        $this->getFromRoute(0, 'organisation', $organisationId);

        $postMap = [ ['action', $action], ['id', $id] ];
        $this->controller->method('getFromPost')->will($this->returnValueMap($postMap));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo($route),
                $this->equalTo(
                    [
                        'action' => strtolower($action),
                        'noteType' => 'note_t_org',
                        'linkedId' => $organisationId,
                        'licence' => null,
                        'case' => null,
                        'application' => null,
                    ]
                ),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->indexAction();
    }

    public function testAddAction()
    {
        $organisationId = 16;
        $noteType  = 'note_t_org';
        $linkedId  = 7;
        $caseId    = null;

        $routeParamMap = [
            ['organisation', $organisationId],
            ['case', $caseId],
            ['noteType', $noteType],
            ['linkedId', $linkedId]
        ];
        $this->controller->method('getFromRoute')
            ->will($this->returnValueMap($routeParamMap));

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('partials/form');

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($this->view));

        $this->mockResponseContent('');
        $this->controller->addAction();
    }
}
