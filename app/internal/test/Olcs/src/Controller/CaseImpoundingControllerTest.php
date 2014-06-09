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
                'processEdit',
                'getServiceLocator',
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
    public function testIndexActionRedirect($action, $id)
    {
        $this->getFrom('Route', 0, 'licence', 7);
        $this->getFrom('Route', 1, 'case', 24);
        $this->getFrom('Post', 2, 'action', $action);
        $this->getFrom('Post', 3, 'id', $id);

        $redirectInfo = $this->getCrudRedirect($action, $id);
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
    public function indexRedirectProvider()
    {
        return array(
            array('add',null),
            array('edit',1)
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
     * Tests the index action. Also covers what happens if the
     * edit button is clicked without a row being selected
     *
     * @dataProvider indexActionProvider
     */
    public function testIndexAction($licenceId, $caseId, $action)
    {
        $this->getFrom('Route', 0, 'licence', $licenceId);
        $this->getFrom('Route', 1, 'case', $caseId);
        $this->getFrom('Post', 2, 'action', $action);
        $this->getFrom('Post', 3, 'id', null);

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($this->getSampleImpoundingArray($licenceId)));

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->getServiceLocatorStaticData()));

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

    public function indexActionProvider()
    {
        return array(
            array(7,24,null),
            array(7,24,'edit')
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

        $form = $this->getFormMock();

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->getLicenceRestCall(),
                    $this->getVenueRestCall()
                )
            );

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('impounding/form'));

        $scriptMock = $this->getMock('\stdClass', ['loadFiles']);
        $scriptMock->expects($this->any())
            ->method('loadFiles')
            ->will($this->returnValue([]));

        $serviceMock = $this->getMock('\stdClass', ['get']);
        $serviceMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($scriptMock));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceMock));

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

        $this->controller->expects($this->exactly(3))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->getSampleImpoundingFormArray(),
                    $this->getLicenceRestCall(),
                    $this->getVenueRestCall()
                )
            );

        $form = $this->getFormMock();

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('setBreadcrumb');

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($this->view));

        $this->view->expects($this->once())
            ->method('setTemplate')
            ->with($this->equalTo('impounding/form'));

        $scriptMock = $this->getMock('\stdClass', ['loadFiles']);
        $scriptMock->expects($this->any())
            ->method('loadFiles')
            ->will($this->returnValue([]));

        $serviceMock = $this->getMock('\stdClass', ['get']);
        $serviceMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($scriptMock));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceMock));

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
    public function processAddEditProvider()
    {
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
    public function processAddEditCancelProvider()
    {
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
     * Information required for a redirect follwing success
     *
     * @param string $action
     * @return array
     */
    private function getCrudRedirect($action, $id)
    {
        return array(
            'string' => 'case_impounding',
            'options' => array(
                'action' => $action,
                'id' => $id,
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
            'Results' => array(
                0 => array(
                    'presidingTc' => array(
                        'name' => 'Name of TC'
                    ),
                    'outcome' => array(
                        'handle' => 'impounding_outcome.1'
                    ),
                    'impoundingType' => array(
                        'handle' => 'impounding_type.1'
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
            'piVenue' => 1,
            'piVenueOther' => 'Other Pi Venue',
            'case' => array
                (
                    'id' => 24
                ),

            'impoundingType' => array
                (
                    'handle' => 'impounding_type.1'
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
            'csrf' => 'e92c3acf055e3e45a131bb46e8a062ca',
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
                    'piVenue' => '1',
                    'piVenueOther' => 'Other Pi Venue'
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

    /**
     * Sample static data
     *
     * @return array
     */
    private function getSampleStaticData()
    {
        return array(
             'impounding_type' => array
             (
                 'impounding_type.1' => 'Hearing',
                 'impounding_type.2' => 'Paperwork only'
             ),

             'impounding_outcome' => array
             (
                 'impounding_outcome.1' => 'Vehicle(s) returned',
                 'impounding_outcome.2' => 'Vehicle(s) not returned'
             ),

             'hearing_location' => array
             (
                 'hearing_location.1' => 'Hearing location 1',
                 'hearing_location.2' => 'Hearing location 2',
                 'hearing_location.3' => 'Hearing location 3'
             ),

             'presiding_tc' => array
             (
                 'presiding_tc.1' => 'Presiding TC 1',
                 'presiding_tc.2' => 'Presiding TC 2',
                 'presiding_tc.3' => 'Presiding TC 3'
             )
        );
    }

    /**
     * Gets a mock version of static-list-data
     */
    private function getServiceLocatorStaticData ()
    {
        $serviceMock = $this->getMock('\stdClass', array('get'));

        $scriptMock = $this->getMock('\stdClass', ['loadFiles']);
        $scriptMock->expects($this->any())
            ->method('loadFiles')
            ->will($this->returnValue([]));

        $serviceMock->expects($this->any())
            ->method('get')
            ->will(
                $this->onConsecutiveCalls(
                    array('static-list-data' => $this->getSampleStaticData()),
                    $scriptMock
                )
            );

        return $serviceMock;
    }

    /**
     * Sample licence rest call
     *
     * @return array
     */
    private function getLicenceRestCall()
    {
        return array(
            'trafficArea' => array(
                'areaCode' => 1
            )
        );
    }

    /**
     * Sample venue rest call
     *
     * @return array
     */
    private function getVenueRestCall()
    {
        return array(
            'Results' => array(
                0 => array(
                    'trafficArea' => array(
                        'areaCode' => 1
                    )
                )
            )
        );
    }

    /**
     *  Gets a form mock
     */
    private function getFormMock()
    {
        $formMock = $this->getMock('\stdClass', array('setData', 'get'));

        $getMock = $this->getMock(
            'stdClass',
            [
                'get'
            ]
        );

        $setValueOptionsMock = $this->getMock(
            'stdClass',
            [
                'setValueOptions'
            ]
        );

        $setValueMock = $this->getMock(
            'stdClass',
            [
                'setValue'
            ]
        );

        $setValueOptionsMock->expects($this->any())
            ->method('setValueOptions')
            ->will($this->returnValue($setValueMock));

        $getMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($setValueOptionsMock));

        $formMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($getMock));

        return $formMock;
    }
}
