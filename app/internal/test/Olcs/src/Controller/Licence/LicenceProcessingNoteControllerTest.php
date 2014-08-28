<?php
/**
 * Licence Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
namespace OlcsTest\Controller\Licence;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Licence Processing Note controller tests
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class LicenceProcessingNoteControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../../config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Licence\Processing\LicenceProcessingNoteController',
            array(
                'makeRestCall',
                'getLoggedInUser',
                'buildTable',
                'getFormWithData',
                'getFromRoute',
                'getFromPost',
                'getView',
                'url',
                'renderView'
            )
        );

        $this->view = $this->getMock(
            '\Zend\View\Model\ViewModel',
            array(
                'setTemplate',
            )
        );

        parent::setUp();
    }

    public function testIndexAction()
    {
        $licenceId = 7;
        $page = 1;
        $sort = 'priority';
        $order = 'desc';
        $limit = 10;

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromPost(1, 'action', null);
        $this->getFromPost(2, 'id', null);
        $this->getFromRouteWithDefault(3, 'page', $page, $page);
        $this->getFromRouteWithDefault(4, 'sort', $sort, $sort);
        $this->getFromRouteWithDefault(5, 'order', $order, $order);
        $this->getFromRouteWithDefault(6, 'limit', $limit, $limit);

        $this->controller->expects($this->once())
            ->method('url');

        $this->controller->expects($this->once())
            ->method('makeRestCall');

        $this->controller->expects($this->once())
            ->method('buildTable');

        $this->controller->expects($this->once())
            ->method('getView')
        ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('renderView');

        $this->controller->indexAction();
    }

    public function testAddAction()
    {
        $licenceId = 7;
        $noteType = 'note_t_lic';

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'noteType', $noteType);

        $this->controller->expects()
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with('licence/processing/notes/form');

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($this->view));

        $this->controller->addAction();
    }

    public function testEditAction()
    {
        $id = 1;
        $note = [
            'comment' => 'comment',
            'id' => $id,
            'priority' => 'Y',
            'version' => 1
        ];


        $this->getFromRoute(0, 'id', $id);

        $this->controller->expects()
            ->method('makeRestCall')
        ->will($this->returnValue($note));

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('renderView')
            ->with($this->equalTo($this->view));

        $this->controller->editAction();
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
     * Generate a fromRoute function call
     *
     * @param int $at
     * @param mixed $with
     * @param mixed $default
     * @param mixed $will
     */
    private function getFromRouteWithDefault($at, $with, $default, $will = false)
    {
        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('getFromRoute')
                ->with(
                    $this->equalTo($with),
                    $this->equalTo($default)
                )
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('getFromRoute')
                ->with(
                    $this->equalTo($with),
                    $this->equalTo($default)
                );
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