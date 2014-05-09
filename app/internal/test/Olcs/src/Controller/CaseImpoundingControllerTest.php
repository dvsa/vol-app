<?php
/**
 * Case Impounding Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Case Impounding Test Controller
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class CaseImpoundingControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseImpoundingController',
            [
                'getView',
                'makeRestCall',
                'getCaseVariables',
                'buildTable',
                'setBreadcrumb',
                'fromRoute',
                'notFoundAction',
                'generateFormWithData',
                'redirectToAction'
            ]
        );

        $this->view = $this->getMock(
            'Zend\View\Model\ViewModel',
            [
                'setTemplate'
            ]
        );

        parent::setUp();
    }

    /**
     * Tests the index action redirects to an action if present
     *
     * @dataProvider indexRedirectProvider
     *
     * @param string $action
     */
    public function testIndexActionRedirect($action)
    {
        $this->getFrom('Route', 0, 'licence', 7);
        $this->getFrom('Route', 1, 'case', 24);
        $this->getFrom('Post', 2, 'action', $action);

        $this->controller->expects($this->once())
            ->method('redirectToAction');

        $this->controller->indexAction();
    }

    /**
     * Data provider for testIndexActionRedirect
     *
     * @return array
     */
    public function indexRedirectProvider(){
        return array(
            array('add'),
            array('edit')
        );
    }

    /**
     * Tests the index action returns not found if no licence or case present
     *
     * @dataProvider indexActionNotFoundProvider
     */
    public function testIndexActionNotFound($licenceId, $caseId)
    {
        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->indexAction();
    }

    /**
     * Tests the index action
     */
    public function testIndexAction()
    {
        $licenceId = 7;
        $caseId = 24;

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);
        $this->getFrom('Post', 2, 'action', null);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($this->getSampleImpoundingArray($licenceId)));

        $this->controller->expects($this->once())
            ->method('buildTable');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('case/manage'));

        $this->assertSame($this->view, $this->controller->indexAction());

        $this->controller->indexAction();
    }

    public function indexActionNotFoundProvider()
    {
        return array(
            array(7,null),
            array(null,24)
        );
    }

    /**
     * Tests the add action
     */
    public function testAddAction()
    {
        $licenceId = 7;
        $caseId = 24;

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('form'));

        $this->assertSame($this->view, $this->controller->addAction());
    }

    /**
     * Tests the edit action
     */
    public function testEditAction()
    {
        $licenceId = 7;
        $caseId = 24;
        $id = 1;

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);
        $this->getFrom('Route', 2, 'id', $id);

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('form'));

        $this->assertSame($this->view, $this->controller->editAction());
    }

    /**
     * Tests the addAction if the licence ID is not found
     */
    /*public function testAddActionNotFoundLicence()
    {
        $licenceId = null;
        $this->getFrom('Route', 0, 'licence', $licenceId);

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->addAction();
    }/*

    /**
     * Tests addAction if no licence information comes back from the rest call
     */
    /*public function testAddActionNoResults()
    {
        $licenceId = 7;
        $this->getFrom('Route', 0, 'licence', $licenceId);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->addAction();
    }*/

    /**
     * Tests editAction
     */
    /*public function testEditAction()
    {
        $licenceId = 7;
        $caseId = 24;
        $caseObject = $this->getSampleCaseArray($caseId, $licenceId);

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        $caseObject
                    ),
                    $this->returnValue(
                        $this->getPageDataRestArray($licenceId)
                    )
                )
            );

        $this->controller->expects($this->once())
            ->method('generateFormWithData');

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorStaticData()));

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('case/edit'));

        $this->assertSame($this->view, $this->controller->editAction());
    }*/

    /**
     * Tests the edit action when no result is found
     */
    /*public function testEditActionNotFound()
    {
        $licenceId = 7;
        $caseId = 24;

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);
        $this->getFrom('Route', 1, 'case', $caseId);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }*/

    /**
     * Creates a mock class (used for the redirect method)
     *
     * @param array $redirectInfo
     * @return type
     */
    private function getRedirectMock($redirectInfo)
    {
        $redirect = $this->getMock('stdClass', ['toRoute']);
        $redirect->expects($this->once())
            ->method('toRoute')
            ->with($this->equalTo($redirectInfo['string']), $this->equalTo($redirectInfo['options']));

        return $redirect;
    }

    /**
     * Information required for a redirect follwing success
     *
     * @param int $licenceId
     * @return array
     */
    private function getSuccessRedirect($licenceId)
    {
        return array(
            'string' => 'licence_case_list',
            'options' => array(
                'licence' => $licenceId,
            )
        );
    }

    /**
     * Shortcut for the getRoute and getPost methods
     *
     * @param string $function
     * @param int $at
     * @param mixed $with
     * @param mixed $will
     */
    private function getFrom($function, $at, $with, $will = false)
    {
        $function = ucwords($function);

        if ($will) {
            $this->controller->expects($this->at($at))
                ->method('from' . $function)
                ->with($this->equalTo($with))
                ->will($this->returnValue($will));
        } else {
            $this->controller->expects($this->at($at))
                ->method('from' . $function)
                ->with($this->equalTo($with));
        }
    }

    /**
     * Gets a mock call to get parameters
     */
    private function getParams ($returnValue)
    {
        $paramsMock = $this->getMock('\stdClass', array('fromPost'));

        $paramsMock->expects($this->once())
            ->method('fromPost')
            ->will($this->returnValue($returnValue));

        return $paramsMock;
    }

    private function getSampleImpoundingArray()
    {
        return array(
            'impoundings' => array(
                0 => array(
                    'presidingTC' => array(
                        'tcName' => 'Name of TC'
                    )
                )
            )
        );
    }
}
