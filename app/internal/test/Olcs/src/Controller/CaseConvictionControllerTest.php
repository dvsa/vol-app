<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
class CaseConvictionControllerTest extends AbstractHttpControllerTestCase
{
    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseConvictionController',
            array(
                'setBreadcrumb',
                'generateFormWithData',
                'generateForm',
                'redirect',
                'makeRestCall',
                'processEdit',
                'processAdd',
                'params',
                'getParams',
                'getView',
                'getTabInformationArray',
                'getCase',
                'generateCommentForm',
                'getCaseSummaryArray',
                'url',
                'getServiceLocator',
                'notFoundAction',
                'fromPost',
                'getRequest'
            )
        );
        parent::setUp();
        $_POST = array();
    }

    public function testIndexRedirectAction()
    {

        $params = $this->getMock('\stdClass', array('fromPost', 'fromRoute'));

        $params->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue(array('licence' => 7, 'case' => 54)));

        $params->expects($this->once())
            ->method('fromPost')
            ->will($this->returnValue(array('action' => 'add', 'table' => 'case_convictions', 'id' => 8)));

        $this->controller->expects($this->exactly(2))
            ->method('params')
            ->will($this->returnValue($params));

        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array('licence_case_list/pagination' => array('licence' => 7)));

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with('case_convictions', array('licence' => 7, 'case' =>  54, 'id' => 8, 'action' => 'add'));

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $viewModel = $this->getMock('\stdClass', array('toRoute'));

        $this->controller->indexAction();

    }

    public function testIndexAction()
    {

        $params = $this->getMock('\stdClass', array('fromPost', 'fromRoute'));

        $params->expects($this->once())
            ->method('fromRoute')
            ->will($this->returnValue(array('licence' => 7, 'case' => 54)));

        $params->expects($this->once())
            ->method('fromPost')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->exactly(2))
            ->method('params')
            ->will($this->returnValue($params));

        $this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array('licence_case_list/pagination' => array('licence' => 7)));

        $viewModel = $this->getMock('\stdClass', array('setVariables', 'setTemplate'));

        $this->controller->expects($this->once())
            ->method('getView')
            ->will($this->returnValue($viewModel));

        $this->controller->expects($this->once())
            ->method('getTabInformationArray')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('generateCommentForm')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('getCaseSummaryArray')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->will($this->returnValue($this->getSampleResultsArray()));

        $this->controller->expects($this->once())
            ->method('url')
            ->will($this->returnValue(array()));

        $configServiceLocator = $this->getMock('\stdClass', array('get'));

        $configServiceLocator->expects($this->once())
            ->method('get')
            ->with('Config')
            ->will($this->returnValue($this->getStaticDefTypes()));

        $serviceLocator = $this->getMock('\stdClass', array('get'));
        $tableBuilder = $this->getMock('\stdClass', array('buildTable'));

        $serviceLocator->expects($this->once())
            ->method('get')
            ->with('Table')
            ->will($this->returnValue($tableBuilder));

        $this->controller->expects($this->exactly(2))
            ->method('getServiceLocator')
            ->will(
                $this->onConsecutiveCalls(
                    $configServiceLocator,
                    $serviceLocator
                )
            );

        $this->controller->indexAction();
    }

    public function testGenerateCommentForm()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseConvictionController',
            array(
                'generateForm',
            )
        );

        $form = $this->getMock('\stdClass', array('setData'));
        $this->controller->expects($this->once())
            ->method('generateForm')
            ->with('conviction-comment', 'saveCommentForm')
            ->will($this->returnValue($form));

        $form->expects($this->once())
            ->method('setData')
            ->will($this->returnValue(54));

        $this->controller->generateCommentForm(54);
    }

    public function testSaveCommentForm()
    {
        $this->controller = $this->getMock(
            '\Olcs\Controller\CaseConvictionController',
            array(
                'processEdit',
                'redirect'
            )
        );

        $data = array('id' => 8, 'convictionData' => array(), 'version' => 1);
        $this->controller->expects($this->once())
            ->method('processEdit')
            ->with($data, 'VosaCase');

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with('case_convictions', [], [], true);

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->saveCommentForm($data);
    }

    private function getSampleResultsArray()
    {
        return array
        (
            'Count' => 3,
            'Results' => array
            (
                0 => array
                (
                    'id' => 25,
                    'dateOfConviction' => '2012-06-15T00:00:00+0100',
                    'categoryText' => 'Category text',
                    'defType' => 'defendant_type.operator',
                    'category' => array(
                        'id' => 48,
                        'description' => 'Category description'
                    )
                )
            )
        );
    }

    private function getStaticDefTypes()
    {
        return array(
            'static-list-data' => array(
                'defendant_types' =>
                [
                    'defendant_type.operator' => 'Operator',
                    'defendant_type.owner' => 'Owner',
                    'defendant_type.partner' => 'Partner',
                    'defendant_type.director' => 'Director',
                    'defendant_type.driver' => 'Driver',
                    'defendant_type.transport_manager' => 'Transport Manager',
                    'defendant_type.other' => 'Other'
                ]
            )
        );
    }

    public function testDealtAction()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('id', 'case', 'licence'))
            ->will($this->returnValue(array('id' => 8, 'case' => 54, 'licence' => 7)));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Conviction', 'GET', array('id' => 8))
            ->will($this->returnValue(array('id' => 8, 'version' => 1, 'dealtWith' => 'N')));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->with(array('id' => 8, 'version' => 1, 'dealtWith' => 'Y'), 'Conviction')
            ->will($this->returnValue(array('id' => 33)));

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_convictions',
                array(
                    'case' =>  54,
                    'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->dealtAction();

    }

    public function testDealtFailedAction()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('id', 'case', 'licence'))
            ->will($this->returnValue(array('case' => 54, 'licence' => 7)));

        $this->controller->expects($this->once())
            ->method('notFoundAction')
            ->will($this->returnValue(null));

        $this->controller->dealtAction();
    }

    public function testAddAction()
    {
        /*$this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array('operators/operators-params' => array('operatorName' => 'a')));*/

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54 )));

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        $this->returnValue(array('id' => 54))
                    ),
                    $this->returnValue(
                        $this->sampleParentCategory()
                    )
                )
            );

        $form = $this->getFormMock();

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->getPostDataMock()));

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

        $this->controller->addAction();
    }

    public function testAddActionFailedWithPostedCategory()
    {
        /*$this->controller->expects($this->once())
            ->method('setBreadcrumb')
            ->with(array('operators/operators-params' => array('operatorName' => 'a')));*/

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54 )));

        $this->controller->expects($this->exactly(3))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        $this->returnValue(array('id' => 54))
                    ),
                    $this->returnValue(
                        $this->sampleParentCategory()
                    ),
                    $this->returnValue(
                        $this->sampleParentCategory()
                    )
                )
            );

        $form = $this->getFormMock();

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will(
                $this->returnValue(
                    $this->getPostDataMock(
                        array(
                            'offence' => array(
                                'category' => 38,
                                'parentCategory' => 1
                            )
                        )
                    )
                )
            );

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

        $this->controller->addAction();
    }

    public function testAddCancelAction()
    {
        $_POST['cancel-conviction'] = '';

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54 )));

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_convictions',
                array(
                    'case' =>  54,
                    'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->addAction();
    }

    public function testAddFailAction()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54 )));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('VosaCase', 'GET', array('id' => 54))
            ->will($this->returnValue(''));

        $this->controller->addAction();
    }

    public function testEditAction()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54, 'id' => 33 )));

        $this->controller->expects($this->exactly(3))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        $this->getSampleConvictionArray()
                    ),
                    $this->returnValue(
                        $this->sampleParentCategory()
                    ),
                    $this->returnValue(
                        $this->sampleParentCategory()
                    )
                )
            );

        $form = $this->getFormMock('\stdClass', array('setData'));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->with('conviction', 'processConviction')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will(
                $this->returnValue(
                    $this->getPostDataMock(
                        array(
                            'offence' => array(
                                'category' => 38,
                                'parentCategory' => 1
                            )
                        )
                    )
                )
            );

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

        $this->controller->editAction();
    }

    public function testEditCancelAction()
    {
        $_POST['cancel-conviction'] = '';

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54 )));

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_convictions',
                array(
                    'case' =>  54,
                    'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->editAction();
    }

    public function testEditFailAction()
    {
        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54, 'id' => 33 )));

        $this->controller->expects($this->once())
            ->method('makeRestCall')
            ->with('Conviction', 'GET', array('id' => 33))
            ->will($this->returnValue(''));

        $this->controller->editAction();
    }

    public function testProcessEditAction()
    {
        $data = array(
            'id' => 33,
            'defendant-details' => array(),
            'offence' => array(),
            'categoryText' => 'Category text',
            'category' => 1
        );

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54, 'action' => 'edit' )));

        $this->controller->expects($this->once())
            ->method('processEdit')
            ->with(array('id' => 33, 'categoryText' => '', 'category' => 1), 'Conviction')
            ->will($this->returnValue(array('id' => 33)));

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_convictions',
                array(
                    'case' =>  54,
                    'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->processConviction($data);
    }

    public function testProcessAddAction()
    {
        $data = array(
            'id' => 33,
            'defendant-details' => array(),
            'offence' => array(),
            'categoryText' => 'Category text',
            'category' => 1
        );

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('action', 'licence', 'case'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54, 'action' => 'add' )));

        $this->controller->expects($this->once())
            ->method('processAdd')
            ->with(array('id' => 33, 'categoryText' => '', 'category' => 1), 'Conviction')
            ->will($this->returnValue(array('id' => 33)));

        $toRoute = $this->getMock('\stdClass', array('toRoute'));

        $toRoute->expects($this->once())
            ->method('toRoute')
            ->with(
                'case_convictions',
                array(
                    'case' =>  54,
                    'licence' => 7
                )
            );

        $this->controller->expects($this->once())
            ->method('redirect')
            ->will($this->returnValue($toRoute));

        $this->controller->processConviction($data);
    }

    /**
     * Tests the categories action
     *
     * @dataProvider categoriesActionProvider
     * @param type $parent
     */
    public function testCategoriesAction($parent)
    {
        $this->getFrom('Post', 0, 'parent', $parent);

        $this->assertInstanceOf('\Zend\Http\PhpEnvironment\Response', $this->controller->categoriesAction());
    }

    /**
     * Data provider for testCategoriesAction
     */
    public function categoriesActionProvider()
    {
        return array(
            array(1),
            array(null)
        );
    }

    /**
     * Returns a sample parent categories array
     *
     * @return array
     */
    private function sampleParentCategory()
    {
        return array(
            'Results' => array(
                0 => array(
                    'id' => 1,
                    'description' => 'Category description'
                )
            )
        );
    }

    private function getSampleConvictionArray()
    {
        return array
        (
            'id' => 32,
            'categoryText' => '',
            'dateOfBirth' => '',
            'dateOfOffence' => '2014-01-01T00:00:00+0000',
            'dateOfConviction' => '2014-01-02T00:00:00+0000',
            'courtFpm' => 'dfdsfdsfsfds',
            'penalty' => 'dfsdfsdfds',
            'costs' => 'sfsdfsdfsd',
            'si' => 'Y',
            'decToTc' => 'Y',
            'personFirstname' => '',
            'personLastname' => '',
            'operatorName' => 'dsfdsfdsfdsfdsf',
            'defType' => 'defendant_type.operator',
            'convictionNotes' => 'sdfsdfdsfsdf',
            'takenIntoConsideration' => 'dsfdsfsdfdsf',
            'person' => '',
            'dealtWith' => 'N',
            'createdOn' => '2014-05-14T14:30:04+0100',
            'lastUpdatedOn' => '2014-05-14T14:30:04+0100',
            'version' => 1,
            'category' => array
            (
                'id' => 28,
                'description' => '',
                'parent' => array
                (
                    'id' => 1
                )

            ),
            'vosaCase' => array
            (
                'id' => 24
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
     *  Gets a form mock
     */
    private function getFormMock()
    {
        $formMock = $this->getMock('\stdClass', array('setData', 'get'));

        $getMock = $this->getMock(
            'stdClass',
            [
                'get',
                'remove'
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
                'setValue',
            ]
        );

        $setValueOptionsMock->expects($this->any())
            ->method('setValueOptions')
            ->will($this->returnValue($setValueMock));

        $getMock->expects($this->any())
            ->method('remove')
            ->will($this->returnValue(true));

        $getMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($setValueOptionsMock));

        $formMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($getMock));

        return $formMock;
    }

    /**
     *  Gets a form mock
     */
    private function getPostDataMock($returnValue = array())
    {
        $getPostMock = $this->getMock(
            'stdClass',
            [
                'getPost',
                'isGet'
            ]
        );

        $getPostMock->expects($this->once())
            ->method('getPost')
            ->will($this->returnValue($returnValue));

        return $getPostMock;
    }

    /**
     * Tests the initial request to test the isGet branch which removes various
     * person search fields
     */
    public function testAddInitialRequestAction()
    {

        $this->controller->expects($this->once())
            ->method('getParams')
            ->with(array('case', 'licence', 'id'))
            ->will($this->returnValue(array ( 'licence' => 7, 'case' => 54 )));

        $this->controller->expects($this->exactly(2))
            ->method('makeRestCall')
            ->will(
                $this->onConsecutiveCalls(
                    $this->returnValue(
                        $this->returnValue(array('id' => 54))
                    ),
                    $this->returnValue(
                        $this->sampleParentCategory()
                    )
                )
            );

        $form = $this->getFormMock();

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($form));

        $this->controller->expects($this->once())
            ->method('getRequest')
            ->will($this->returnValue($this->getGetDataMock(true)));

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

        $this->controller->addAction();
    }

    /**
     *  Gets a form mock via GET
     */
    private function getGetDataMock($returnValue = array())
    {
        $getPostMock = $this->getMock(
            'stdClass',
            [
                'isGet',
                'getPost'
            ]
        );

        $getPostMock->expects($this->once())
            ->method('isGet')
            ->will($this->returnValue($returnValue));

        return $getPostMock;
    }
}
