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
                'fromPost',
                'notFoundAction',
                'redirect',
                'generateFormWithData',
                'processAdd',
                'processEdit'
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

        $redirectInfo = $this->getActionRedirect($action);
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

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
            ->method('makeRestCall')
            ->will($this->returnValue($this->getSampleImpoundingFormArray()));

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
     * Tests the not found action is called when the record being edited is not found
     */
    public function testEditActionNotFound()
    {
        $licenceId = 7;
        $caseId = 24;
        $id = 1;

        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);
        $this->getFrom('Route', 2, 'id', $id);

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('notFoundAction');

        $this->controller->editAction();
    }

    /**
     * Tests processAddImpounding
     *
     * @dataProvider processAddEditProvider
     */
    public function testProcessAddImpounding($data)
    {
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(array('id' => 1)));

        $redirectInfo = $this->getAddSuccessRedirect();
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processAddImpounding($data);

    }

    /**
     * Tests processAddImpounding does the correct redirect on failure
     *
     * @dataProvider processAddEditProvider
     */
    public function testProcessAddImpoundingFail($data)
    {
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->will($this->returnValue(array()));

        $redirectInfo = $this->getActionRedirect('add');
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processAddImpounding($data);
    }

    /**
     * Tests processAddImpounding does the correct redirect on cancel
     *
     * @dataProvider processAddEditCancelProvider
     */
    public function testProcessAddImpoundingCancel($data)
    {
        $redirectInfo = $this->getActionRedirect('add');
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->at(0))
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processAddImpounding($data);
    }

    /**
     * Tests processAddImpounding
     *
     * @dataProvider processAddEditProvider
     */
    public function testProcessEditImpounding($data)
    {
        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(array()));

        $redirectInfo = $this->getEditSuccessRedirect();
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processEditImpounding($data);
    }

    /**
     * Tests processEditImpounding does the correct redirect on failure
     *
     * @dataProvider processAddEditProvider
     */
    public function testProcessEditImpoundingFail($data)
    {
        $this->controller->expects($this->once())
            ->method('processEdit')
            ->will($this->returnValue(array('fail' => 1)));

        $redirectInfo = $this->getActionRedirect('edit');
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processEditImpounding($data);
    }

    /**
     * Tests processEditImpounding does the correct redirect on cancel
     *
     * @dataProvider processAddEditCancelProvider
     */
    public function testProcessEditImpoundingCancel($data)
    {
        $redirectInfo = $this->getActionRedirect('edit');
        $redirect = $this->getRedirectMock($redirectInfo);

        $this->controller->expects($this->at(0))
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processEditImpounding($data);
    }

    /**
     * Data provider for add/edit process
     *
     * @return array
     */
    public function processAddEditProvider(){
        return array(
            array(
                $this->getSampleImpoundingPostData(true)
            )
        );
    }

    /**
     * Data provider for add/edit cancel
     *
     * @return array
     */
    public function processAddEditCancelProvider(){
        return array(
            array(
                $this->getSampleImpoundingPostData(false)
            )
        );
    }

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
     * Information required for a redirect following add success
     *
     * @return array
     */
    private function getAddSuccessRedirect()
    {
        return array(
            'string' => 'case_impounding',
            'options' => array(
                'action' => null,
            )
        );
    }

    /**
     * Information required for a redirect following edit success
     *
     * @return array
     */
    private function getEditSuccessRedirect()
    {
        return array(
            'string' => 'case_impounding',
            'options' => array(
                'action' => null,
                'id' => null,
            )
        );
    }

    /**
     * Information required for a redirect follwing success
     *
     * @param string $action
     * @return array
     */
    private function getActionRedirect($action)
    {
        return array(
            'string' => 'case_impounding',
            'options' => array(
                'action' => $action,
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

    private function getSampleImpoundingArray()
    {
        return array(
            'impoundings' => array(
                0 => array(
                    'presidingTc' => array(
                        'tcName' => 'Name of TC'
                    ),
                    'outcome' => array(
                        'handle' => 'Name of Outcome'
                    ),
                )
            ),
        );
    }

    private function getSampleImpoundingFormArray()
    {
        return array(
            'id' => 5,
            'hearingDate' => '2011-03-05T09:05:00+0000',
            'applicationReceiptDate' => '2010-04-05T00:00:00+0100',
            'outcomeSentDate' => '1998-03-16T00:00:00+0000',
            'notes' => 'dgjdhdhfd',
            'version' => 1,
            'case' => array
                (
                    'id' => 24
                ),

            'impoundingType' => array
                (
                    'handle' => 'impounding_type.1'
                ),

            'hearingLocation' => array
                (
                    'handle' => 'hearing_location.1'
                ),

            'presidingTc' => array
                (
                    'id' => 1
                ),

            'outcome' => array
                (
                    'handle' => 'impounding_outcome.2'
                )
        );
    }

    /**
     * Sample postdata for impoundings add/edit
     *
     * @param bool $submit
     * @return type
     */
    private function getSampleImpoundingPostData($submit = true)
    {
        return array(
            'case' => 24,
            'id' => 5,
            'version' => 2,
            'crsf' => 'e92c3acf055e3e45a131bb46e8a062ca',
            'submit' => ($submit ? '' : false),
            'cancel' => ($submit ? false : ''),
            'application_details' => array
                (
                    'impoundingType' => 'impounding_type.1',
                    'applicationReceiptDate' => '2010-04-05',
                ),

            'hearing' => array
                (
                    'hearingDate' => '2011-03-05',
                    'hearingTime' => '09:05',
                    'hearingLocation' => 'hearing_location.1'
                ),

            'outcome' => array
                (
                    'presidingTc' => 'presiding_tc.1',
                    'outcome' => 'impounding_outcome.2',
                    'outcomeSentDate' => '1998-03-16',
                    'notes' => 'dgjdhdhfd'
                ),

        );
    }
}
