<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
class SubmissionControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'getServiceLocator',
            'setBreadcrumb',
            'generateFormWithData',
            'generateForm',
            'redirect',
            'params',
            'getParams',
            'makeRestCall',
            'setData',
            'processEdit',
            'processAdd',
            'getViewModel',
            'createSubmission',
            'getSubmissionView',
            'getRequest',
            'url',
            'setSubmissionBreadcrumb',
            'getLoggedInUser'
            )
        );
        $this->controller->routeParams = array();
        $this->licenceData = array(
            'id' => 7,
            'licenceType' => 'Standard National',
            'goodsOrPsv' => 'Psv'
        );

        parent::setUp();
    }

    public function testAddPostAction()
    {
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add');

        $this->controller->expects($this->once())
            ->method('setSubmissionBreadcrumb')
            ->with();

        $this->controller->expects($this->once())
            ->method('createSubmission')
            ->with($this->controller->routeParams)
            ->will($this->returnValue('{"submission":{}}'));

        $getRequest = $this->getMock('\stdClass', array('isPost'));

        $getRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($getRequest));

        $data = array(
            'createdBy' => 1,
            'text' => '{"submission":{}}',
            'vosaCase' => 54);

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->with($data, 'Submission')
            ->will($this->returnValue(8));

        $redirect = $this->getMock('\stdClass', array('toRoute'));

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', array('licence' => 7, 'case' => 54, 'id' => null, 'action' => 'edit'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->addAction();
    }

    public function testAddGetAction()
    {
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add');

        $this->controller->expects($this->once())
            ->method('setSubmissionBreadcrumb')
            ->with();

        $this->controller->expects($this->once())
            ->method('createSubmission')
            ->with($this->controller->routeParams)
            ->will($this->returnValue('{"submission":{}}'));

        $getRequest = $this->getMock('\stdClass', array('isPost'));

        $getRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($getRequest));

        $this->controller->expects($this->once())
            ->method('getSubmissionView')
            ->with(array('data' => array('submission' => array())))
            ->will($this->returnValue('view}'));

        $this->controller->addAction();
    }

    public function testEditPostAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'getEditSubmissionData',
            'getSubmissionView',
            'getRequest',
            'setSubmissionBreadcrumb'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add');

        $this->controller->expects($this->once())
            ->method('setSubmissionBreadcrumb')
            ->with();

        $getRequest = $this->getMock('\stdClass', array('isPost'));

        $getRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(false));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($getRequest));

        $this->controller->expects($this->once())
            ->method('getEditSubmissionData')
            ->will($this->returnValue('{"submission":{}}'));

        $this->controller->expects($this->once())
            ->method('getSubmissionView')
            ->with('{"submission":{}}');

        $this->controller->editAction();
    }

    public function testEditRedirectAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'getEditSubmissionData',
            'getSubmissionView',
            'getRequest',
            'redirect',
            'params',
            'setSubmissionBreadcrumb'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add');

        $this->controller->expects($this->once())
            ->method('setSubmissionBreadcrumb')
            ->with();

        $getRequest = $this->getMock('\stdClass', array('isPost'));

        $getRequest->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($getRequest));

        $redirect = $this->getMock('\stdClass', array('toRoute'));

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', array('licence' => 7, 'case' => 54, 'id' => null, 'action' => 'edit'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $params = $this->getMock('\stdClass', array('fromPost'));

        $params->expects($this->once())
            ->method('fromPost')
            ->with('id')
            ->will($this->returnValue(null));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($params));

        $this->controller->editAction();
    }

    public function testGetEditSubmissionData()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'makeRestCall',
            'getServiceLocator',
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'add', 'id' => 8);
        $bundle = array(
            'children' => array(
                'submissionActions' => array(
                    'properties' => 'ALL',
                    'children' => array(
                        'userSender' => array(
                            'properties' => 'ALL'
                        ),
                        'userRecipient' => array(
                            'properties' => 'ALL'
                        ),
                        'piReasons' => array(
                            'properties' => 'ALL'
                        ),
                    )
                )
            )
        );

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Submission', 'GET', array('id' => $this->controller->routeParams['id']), $bundle)
            ->will(
                $this->returnValue(
                    array(
                        'text' => '{"submission":{}}',
                        'submissionActions' => array(
                            array(
                                'submissionActionType' => 'decision',
                                'submissionActionStatus' => 'submission_decision.disagree'
                            )
                        )
                    )
                )
            );

        $serviceLocator = $this->getMock('\stdClass', array('get'));

        $serviceLocator->expects($this->once())
            ->method('get')
            ->with('config')
            ->will(
                $this->returnValue(
                    array(
                        'static-list-data' => array(
                            'submission_decision' => array(
                                'submission_decision.disagree' => 'Disagree'
                            )
                        )
                    )
                )
            );

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($serviceLocator));

        $this->controller->getEditSubmissionData();
    }

    public function testgetSubmissionView()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'getViewModel',
            'url',
            'getSubmissionSectionViews'
            )
        );

        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'post', 'id' => 8);
        $this->controller->submissionConfig = array('sections' => array());
        $this->controller->expects($this->once())
            ->method('getSubmissionSectionViews')
            ->with(array())
            ->will($this->returnValue(array()));

        $url = $this->getMock('\stdClass', array('fromRoute'));

        $url->expects($this->once())
            ->method('fromRoute')
            ->with('submission', $this->controller->routeParams)
            ->will($this->returnValue('/licence/7/case/28/submission/edit/166'));

        $this->controller->expects($this->once())
            ->method('url')
            ->will($this->returnValue($url));

        $viewModel = $this->getMock('\stdClass', array('setTemplate'));

        $viewModel->expects($this->once())
            ->method('setTemplate')
            ->with('submission/page');

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->with(
                array(
                    'params' => array(
                        'formAction' => '/licence/7/case/28/submission/edit/166',
                        'routeParams' => $this->controller->routeParams,
                        'pageTitle' => 'case-submission',
                        'pageSubTitle' => 'case-submission-text',
                        'submission' => array('data' => array(), 'views' => array()),
                        'submissionConfig' => array()
                    )
                )
            )
            ->will($this->returnValue($viewModel));

        $this->controller->getSubmissionView(array('data' => array()));
    }

    public function testPostDecisionAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'getViewModel',
            'params',
            'redirect',
            'backToCaseButton'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'decision', 'id' => 8);

        $params = $this->getMock('\stdClass', array('fromPost'));

        $params->expects($this->at(0))
            ->method('fromPost')
            ->with('decision')
            ->will($this->returnValue(true));

        $this->controller->expects($this->atLeastOnce())
            ->method('params')
            ->will($this->returnValue($params));

        $redirect = $this->getMock('\stdClass', array('toRoute'));

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', $this->controller->routeParams);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->postAction();
    }

    public function testPostRecommendAction()
    {
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'recommendation', 'id' => 8);
        $params = $this->getMock('\stdClass', array('fromPost'));

        $params->expects($this->at(0))
            ->method('fromPost')
            ->with('decision')
            ->will($this->returnValue(false));

        $params->expects($this->at(1))
            ->method('fromPost')
            ->with('recommend')
            ->will($this->returnValue(true));

        $this->controller->expects($this->atLeastOnce())
            ->method('params')
            ->will($this->returnValue($params));

        $redirect = $this->getMock('\stdClass', array('toRoute'));

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', $this->controller->routeParams);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->postAction();
    }

    public function testRecommendationAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'backToCaseButton',
            'formView',
            'setSubmissionBreadcrumb',
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'recommendation', 'id' => 8);

        $this->controller->expects($this->once())
            ->method('setSubmissionBreadcrumb')
            ->with();

        $this->controller->expects($this->once())
            ->method('formView')
            ->with('recommend');

        $this->controller->recommendationAction();
    }

    public function testDecisionAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'backToCaseButton',
            'formView',
            'setSubmissionBreadcrumb',
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'recommendation', 'id' => 8);

        $this->controller->expects($this->once())
            ->method('setSubmissionBreadcrumb')
            ->with();

        $this->controller->expects($this->once())
            ->method('formView')
            ->with('decision');

        $this->controller->decisionAction();
    }

    public function testFormView()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'makeRestCall',
            'getServiceLocator',
            'getFormWithListData',
            'formPost',
            'getViewModel',
            'getLoggedInUser'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'decision', 'id' => 8);

        $this->controller->expects($this->once())
            ->method('getLoggedInUser')
            ->will($this->returnValue(1));

        $this->controller->expects($this->once())
            ->method('getFormWithListData')
            ->with('decision', array('submission' => 8, 'userSender' => 1))
            ->will($this->returnValue('form'));

        $this->controller->expects($this->once())
            ->method('formPost')
            ->with('form', 'processRecDecForm')
            ->will($this->returnValue('form'));

        $viewModel = $this->getMock('\stdClass', array('setTemplate'));

        $viewModel->expects($this->once())
            ->method('setTemplate')
            ->with('form');

        $this->controller->expects($this->once())
            ->method('getViewModel')
            ->with(
                array(
                    'form' => 'form',
                    'params' => array(
                        'pageTitle' => "submission-decision",
                        'pageSubTitle' => "submission-decision-text",
                    )
                )
            )
            ->will($this->returnValue($viewModel));

        $this->controller->formView('decision');
    }

    public function testProcessRecDecForm()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'processAdd',
            'redirect'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'decision', 'id' => 8);
        $this->controller->expects($this->once())
            ->method('processAdd')
            ->with(array('main' => array()))
            ->will($this->returnValue(array('id' => 8)));

        $redirect = $this->getMock('\stdClass', array('toRoute'));

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('case_manage', array('licence' => 7, 'case' => 54, 'tab' => 'overview'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->processRecDecForm(array('main' => array()));
    }

    public function testBackToCaseButton()
    {
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'decision', 'id' => 8);
        $redirect = $this->getMock('\stdClass', array('toRoute'));

        $redirect->expects($this->once())
            ->method('toRoute')
            ->with('submission', array('licence' => 7, 'case' => 54, 'id' => 8, 'action' => 'edit'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($redirect));

        $this->controller->backToCaseButton(array('main' => array()));
    }

    /**
     * Tests that a notFoundAction results if we have an invalid licence type
     */
    public function testLicenceTypeNotFound()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
                'notFoundAction',
                'makeRestCall',
                'getForm'
            )
        );

        $this->controller->routeParams = array('licence' => 7);

        $this->controller->expects($this->once())
                ->method('getForm')
                ->will($this->returnValue($this->getFormMock()));
        
        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->getUserListRestCall(),
                    $this->getLicenceRestCall(1, 'InvalidLicenceType')
                )
            );

        $this->controller->expects($this->once())
                ->method('notFoundAction');

        $this->controller->getFormWithListData('decision', array());
    }

    /**
     * @dataProvider getFormWithListDataProvider
     */
    public function testGetFormWithListData($licenceId, $niFlag, $goodsOrPsv)
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
                'getForm',
                'makeRestCall'
            )
        );

        $this->controller->routeParams = array('licence' => $licenceId);

        $this->controller->expects($this->exactly(3))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->getUserListRestCall(),
                    $this->getLicenceRestCall($niFlag, $goodsOrPsv),
                    $this->getPiReasonsRestCall()
                )
            );

        $this->controller->expects($this->once())
                ->method('getForm')
                ->will($this->returnValue($this->getFormMock()));

        $this->controller->getFormWithListData('decision', array());
    }

    public function getFormWithListDataProvider()
    {
        return
        [
            [7, 1, 'goods'],
            [7, 0, 'psv']
        ];
    }

    public function testSetSubmissionBreadcrumb()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'makeRestCall',
            'setBreadcrumb'
            )
        );
        $this->controller->routeParams = array('case' => 54, 'licence' => 7, 'action' => 'decision', 'id' => 8);
        $thisNavRoutes = array(
            'licence_case_list/pagination' => array('licence' => 7),
            'case_manage' => array(
                'case' => 54,
                'licence' => 7,
                'tab' => 'overview'
            )
        );
        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with($thisNavRoutes);

        $this->controller->setSubmissionBreadcrumb(array());
    }

    public function testAddNoteAction()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'makeRestCall',
            'setBreadcrumb',
            'params',
            'getRequest',
            'redirect'
            )
        );

        $this->controller->routeParams = array(
            'case' => 54,
            'licence' => 7,
            'action' => 'add',
            'id' => 8,
            'type' => 'submission',
            'section' => 'case-summary-info',
            'typeId' => 8);

        $obj = $this->getMock('\stdClass', array('fromPost', 'toRoute', 'isPost'));

        $obj->expects($this->once())
            ->method('fromPost')
            ->will($this->returnValue(array('section' => 'case-summary-info')));

        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($obj));

        $obj->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(true));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($obj));

        $routeParams = array(
            'case' => 54,
            'licence' => 7,
            'action' => 'add',
            'type' => 'submission',
            'section' => 'case-summary-info',
            'typeId' => 8);

        $obj->expects($this->once())
            ->method('toRoute')
            ->with('note', $routeParams);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($obj));

        $this->controller->addnoteAction();
    }

    public function testAddNoteNoPost()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
                'getRequest',
                'params'
            )
        );

        $obj = $this->getMock('\stdClass', array('fromPost', 'isPost'));

        $obj->expects($this->once())
            ->method('fromPost')
            ->will($this->returnValue(array()));
        
        $obj->expects($this->once())
            ->method('isPost')
            ->will($this->returnValue(false));
        
        $this->controller->expects($this->once())
            ->method('params')
            ->will($this->returnValue($obj));
        
        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($obj));

        $this->controller->addnoteAction();
    }

    public function testGetSubmissionSectionViews()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\Submission\SubmissionController', array(
            'makeRestCall',
            'setBreadcrumb',
            'params',
            'getRequest',
            'redirect',
            'getServiceLocator',
            'getViewModel'
            )
        );

        $this->controller->submissionConfig = array(
            'sections' => array(
                'case-summary-info' => array(
                    'view' => 'submission/partials/case-summary',
                    'dataPath' => 'VosaCase',
                    'bundle' => array(
                        'children' => array(
                            'categories' => array(
                                'properties' => array(
                                    'id',
                                    'name'
                                )
                            ),
                            'convictions' => array(
                                'properties' => 'ALL'
                            ),
                            'licence' => array(
                                'properties' => 'ALL',
                                'children' => array(
                                    'trafficArea' => array(
                                        'properties' => 'ALL'
                                    ),
                                    'organisation' => array(
                                        'properties' => 'ALL'
                                    )
                                )
                            )
                        )
                    )
                )
            )
        );

        $obj = $this->getMock('\stdClass', array('get', 'setTemplate'));

        $viewRenderer = $this->getMock('\stdClass', array('render'));

        $viewRenderer->expects($this->at(1))
            ->method('render')
            ->with($obj)
            ->will($this->returnValue('rendered HTML'));

        $obj->expects($this->once())
            ->method('get')
            ->with('ViewRenderer')
            ->will($this->returnValue($viewRenderer));

        $this->controller->expects($this->once())
            ->method('getServiceLocator')
            ->will($this->returnValue($obj));

        $obj->expects($this->at(1))
            ->method('setTemplate')
            ->with('submission/partials/case-summary');

        $obj->expects($this->at(2))
            ->method('setTemplate')
            ->with('submission/partials/blank');

        $this->controller->expects($this->atLeastOnce())
            ->method('getViewModel')
            ->with(array('sectionData' => []))
            ->will($this->returnValue($obj));

        $this->controller->getSubmissionSectionViews(
            array(
                'case-summary-info' => array('data' => [], 'notes' => []),
                'persons' => array('data' => [], 'notes' => [])
            )
        );
    }

    /**
     * Sample Pi Reasons rest call
     *
     * @return array
     */
    private function getPiReasonsRestCall()
    {
        return
        [
            'Results' => [
                [
                    'id' => 0,
                    'sectionCode' => 'Section code',
                    'description' => 'Description'
                ]
            ]
        ];
    }

    /**
     * Sample user list rest call
     *
     * @return array
     */
    private function getUserListRestCall()
    {
        return
        [
            'Results' => [
                [
                    'id' => 0,
                    'name' => 'User name'
                ]
            ]
        ];
    }

    /**
     * Sample licence rest call
     *
     * @return array
     */
    private function getLicenceRestCall($niFlag, $goodsOrPsv)
    {
        return [
            'niFlag' => $niFlag,
            'goodsOrPsv' => $goodsOrPsv
        ];
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
                'setValueOptions',
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
