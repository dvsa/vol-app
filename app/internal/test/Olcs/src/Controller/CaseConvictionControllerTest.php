<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc
 */

namespace OlcsTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class CaseConvictionControllerTest  extends AbstractHttpControllerTestCase
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
                'params',
                'getView',
                'getTabInformationArray',
                'getCase',
                'generateCommentForm',
                'getCaseSummaryArray',
                'url',
                'getServiceLocator'
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
}
