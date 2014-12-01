<?php
/**
 * Licence Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\Licence;

use OlcsTest\Controller\ProcessingNoteControllerTestAbstract;

/**
 * Licence Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class LicenceProcessingNoteControllerTest extends ProcessingNoteControllerTestAbstract
{
    protected $testClass = '\Olcs\Controller\Licence\Processing\LicenceProcessingNoteController';
    protected $mainIdRouteParam = 'licence';

    public function testAddAction()
    {
        $licenceId = 7;
        $noteType  = 'note_t_lic';
        $linkedId  = null;
        $caseId    = null;

        $routeParamMap = [
            ['licence', 'case', 'noteType', 'linkedId'],
            [$licenceId, $caseId, $noteType, $linkedId]
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
            ->with($this->controller->getTemplatePrefix() . '/notes/form');

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($this->view));

        $this->controller->addAction();
    }

    /**
     * Tests for a crud add redirect from index action
     */
    public function testIndexActionAddRedirect()
    {
        $licenceId = 7;
        $action = 'Add';
        $id = null;
        $route = $this->controller->getRoutePrefix() . '/add-note';

        $this->getFromRoute(0, $this->mainIdRouteParam, $licenceId);
        $this->getFromPost(1, 'action', $action);
        $this->getFromPost(2, 'id', $id);

        $this->controller->expects($this->once())
            ->method('redirectToRoute')
            ->with(
                $this->equalTo($route),
                $this->equalTo(
                    [
                        'action' => strtolower($action),
                        'licence' => $licenceId,
                        'noteType' => 'note_t_lic',
                        'linkedId' => $licenceId,
                        'case' => null,
                        'application' => null

                    ]
                ),
                $this->equalTo([]),
                $this->equalTo(true)
            );

        $this->controller->indexAction();
    }
}
