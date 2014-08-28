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
                'getTable',
                'getLicence',
                'getRequest',
                'getForm',
                'getFromRoute',
                'getFromPost',
                'params',
                'getServiceLocator',
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
        //$this->controller->indexAction();
        $this->assertEquals($this->view, $this->controller->indexAction());
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
                ->method('fromRoute')
                ->with(
                    $this->equalTo($with),
                    $this->equalTo($default)
                )
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('fromRoute')
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
                ->method('fromPost')
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('fromPost')
                ->with($this->equalTo($with));
        }
    }
}