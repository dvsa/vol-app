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
                'getView',
                'url',
                'renderView',
                'redirectToRoute',
                'processAdd',
                'processEdit',

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

    /**
     * This uses the LicenceNoteTrait which is tested elsewhere, so only basic tests needed
     */
    public function testIndexAction()
    {
        $licenceId = 110;
        $page = 1;
        $sort = 'priority';
        $order = 'desc';
        $limit = 10;

        $this->getFromRoute(0, 'licence', $licenceId);
        $this->getFromRoute(1, 'busRegId', null);
        $this->getFromPost(2, 'action', null);
        $this->getFromPost(3, 'id', null);
        $this->getFromRouteWithDefault(4, 'page', $page, $page);
        $this->getFromRouteWithDefault(5, 'sort', $sort, $sort);
        $this->getFromRouteWithDefault(6, 'order', $order, $order);
        $this->getFromRouteWithDefault(7, 'limit', $limit, $limit);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($this->getSampleResult()));

        $this->controller->expects($this->once())
            ->method('getTable');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate');

        $this->controller->expects($this->once())
            ->method('renderView');

        $this->controller->indexAction();
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
