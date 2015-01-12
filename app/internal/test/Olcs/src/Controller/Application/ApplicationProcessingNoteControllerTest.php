<?php
/**
 * Application Processing Note controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Application;

use OlcsTest\Controller\ProcessingNoteControllerTestAbstract;

/**
 * Application Processing Note controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class ApplicationProcessingNoteControllerTest extends ProcessingNoteControllerTestAbstract
{
    protected $testClass = '\Olcs\Controller\Application\Processing\ApplicationProcessingNoteController';
    protected $mainIdRouteParam = 'application';

    public function testIndexAction()
    {
        $this->mockApplicationEntityService();
        parent::testIndexAction();
    }

    /**
     * Tests for a crud add redirect from index action
     */
    public function testIndexActionAddRedirect()
    {
        $this->mockApplicationEntityService();

        $licenceId = 7;
        $applicationId = 123;
        $action = 'Add';
        $id = null;
        $route = $this->controller->getRoutePrefix() . '/add-note';

        $this->getFromRoute(0, 'application', $applicationId);

        $postMap = [ ['action', $action], ['id', $id] ];
        $this->controller->method('getFromPost')->will($this->returnValueMap($postMap));

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo($route),
                $this->equalTo(
                    [
                        'action' => strtolower($action),
                        'application' => $applicationId,
                        'noteType' => 'note_t_app',
                        'licence' => $licenceId,
                        'linkedId' => $licenceId, //linkedId is redundant here but the redirect function is generic
                        'case' => null

                    ]
                ),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->indexAction();
    }

    public function testAddAction()
    {
        $this->mockApplicationEntityService();

        $applicationId = 16;
        $noteType  = 'note_t_app';
        $linkedId  = 7;
        $caseId    = null;

        $routeParamMap = [
            ['application', $applicationId],
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

    /**
     * Tests for a crud edit/delete redirect from index action
     *
     * @dataProvider indexActionModifyRedirectProvider
     *
     * @param string $action
     */
    public function testIndexActionModifyRedirect($action)
    {
        $this->mockApplicationEntityService();
        parent::testIndexActionModifyRedirect($action);
    }

    /**
     * Mocks the service locator and application entity service
     */
    private function mockApplicationEntityService()
    {
        $mock = $this->getMock(
            '\StdClass',
            ['getLicenceIdForApplication']
        );
        $mock->expects($this->any())
            ->method('getLicenceIdForApplication')
            ->will($this->returnValue(7));

        $mockServiceLocator = $this->getMock('\StdClass', ['get']);
        $mockServiceLocator->expects($this->once())
            ->method('get')
            ->with('Entity\Application')
            ->will($this->returnValue($mock));
        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($mockServiceLocator));
    }
}
