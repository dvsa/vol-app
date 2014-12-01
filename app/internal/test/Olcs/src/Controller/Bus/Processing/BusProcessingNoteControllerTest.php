<?php
/**
 * Bus Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Licence;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Bus Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class BusProcessingNoteControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Bus\Processing\BusProcessingNoteController',
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
                'loadScripts'
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
     * This uses the LicenceNoteTrait which is tested elsewhere, so only basic tests needed
     */
    public function testIndexAction()
    {
        $licenceId = 110;

        $table = $this->getMock(
            'Common\Service\Table\TableBuilder', [], [], '', false
        );

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'busRegId', null);
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
            ->with(['forms/filter']);

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
     * Tests for a crud add redirect from index action
     */
    public function testIndexActionAddRedirect()
    {
        $licenceId = 7;
        $linkedId = 1;
        $action = 'Add';
        $busRegId = 1;
        $id = null;
        $route = $this->controller->getRoutePrefix() . '/add-note';

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'busRegId', $busRegId);
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
                        'noteType' => 'note_t_bus',
                        'linkedId' => $linkedId,
                        'case' => null,
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
        $busRegId = 1;
        $route = $this->controller->getRoutePrefix() . '/modify-note';

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'busRegId', $busRegId);
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

    public function indexActionModifyRedirectProvider()
    {
        return [
            ['Edit', 'Delete']
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

    private function getSampleResult()
    {
        return [
            'Results' => [
                0 => [
                    'noteType' => [
                        'id' => 'note_t_bus',
                        'description' => 'Bus Registration'
                    ]
                ]
            ]
        ];
    }
}
