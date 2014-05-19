<?php

namespace OlcsTest\Controller\Business;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use Zend\ServiceManager\ServiceManager;
use Zend\Mvc\Service\ServiceManagerConfig;
use Zend\Form\Form;
use Zend\Form\Element;
use Zend\Form\Fieldset;

class IndexControllerTest extends AbstractHttpControllerTestCase
{

    protected $controller;

    protected $applicationId = 1;
    protected $step = 'business-type';

    protected function setUpMockController($methods)
    {
        $this->controller = $this->getMock(
            '\SelfServe\Controller\BusinessType\IndexController',
            $methods
        );
    }

    protected function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__.'/../../../../config/application.config.php'
        );

        parent::setUp();

    }


    private function formGeneratorMocking()
    {

        $fieldset = new \Zend\Form\Fieldset();
        $fieldset->add([
            'name' => 'edit_business_type',
        ]);

        $formMock = $this->getMock('\Zend\Form\Form', array('get'));
        $formMock->expects($this->any())
            ->method('get')
            ->will($this->returnValue($fieldset));


        $mock = $this->getMock('\stdClass', [
            'getFormConfig', 'setFormConfig', 'createForm', 'addFieldset',
        ]);
        $mock->expects($this->any())
            ->method('createForm')
            ->will($this->returnValue($formMock));

        $mock->expects($this->any())
            ->method('getFormConfig')
            ->will($this->returnValue(array(
                'business-type' => ['fieldsets' => []],
            )));

        return $mock;
    }


    public function testBusinessTypeWithNoTypePersisted()
    {
        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'generateSectionForm',
            'getPersistedFormData',
            'getFormGenerator',
        ));

        $formMock = $this->formGeneratorMocking();

        $this->controller->expects($this->once())
            ->method('getFormGenerator')
            ->will($this->returnValue($formMock));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValueMap($this->fromRouteMap()));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->makeRestCallMap()));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));

        $this->controller->generateStepFormAction();
    }

    /**
     * @group thecurrent
     */
    public function testDetailsTradersWithTypePersisted()
    {
        $this->step = 'details';
        $this->applicationId = 2;

        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'getPersistedFormData',
            'getFormGenerator',
            'getUrlFromRoute',
        ));

        $params = new \Zend\Stdlib\Parameters();
        $params->set('sole-trader', ['trading_names' => ['submit_add_trading_name' => '']]);
        $this->controller->getRequest()->setPost($params);

        $formMock = $this->formGeneratorMocking();

        $this->controller->expects($this->once())
            ->method('getFormGenerator')
            ->will($this->returnValue($formMock));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValueMap($this->fromRouteMap()));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->makeRestCallMap()));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));

        $this->controller->generateStepFormAction();
    }

    /**
     * @group thecurrent
     */
    public function testDetailsWithTypePersisted()
    {
        $this->step = 'details';
        $this->applicationId = 2;

        $this->setUpMockController(array(
            'params',
            'makeRestCall',
            'getPersistedFormData',
            'getFormGenerator',
            'getUrlFromRoute',
        ));


        $formMock = $this->formGeneratorMocking();

        $this->controller->expects($this->once())
            ->method('getFormGenerator')
            ->will($this->returnValue($formMock));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValueMap($this->fromRouteMap()));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->makeRestCallMap()));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));


        $this->controller->generateStepFormAction();
    }

    /**
     * @group thecurrent
     */
    public function testGetBusinessTypeFormData()
    {
        $this->applicationId = 2;
        $this->setUpMockController(array(
            'params',
            'makeRestCall',
            'getPersistedFormData',
            'getFormGenerator',
            'getUrlFromRoute',
        ));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValueMap($this->fromRouteMap()));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->makeRestCallMap()));

        $result = $this->controller->getBusinessTypeFormData();
        $this->assertArrayHasKey('business-type', $result);
    }

    public function testStepWithNoTypePersisted()
    {
        $this->step = 'sole-trader';
        $this->setUpMockController(array(
            'params',
            'makeRestCall',
            'redirect'
        ));

        $mockParams = $this->getMock('\stdClass', array('fromRoute'));
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->will($this->returnValueMap($this->fromRouteMap()));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('makeRestCall')
            ->will($this->returnValueMap($this->makeRestCallMap()));

        $responseMock = $this->getMock('\Zend\Http\Response');

        $mockRedirect = $this->getMock('\stdClass', array('toRoute'));
        $mockRedirect->expects($this->any())
            ->method('toRoute')
            ->will($this->returnValue($responseMock));

        $this->controller->expects($this->any())
            ->method('redirect')
            ->will($this->returnValue($mockRedirect));

        $action = $this->controller->generateStepFormAction();
        $this->assertInstanceOf('\Zend\Http\Response', $action);
    }


    private function fromRouteMap()
    {
        return [
            ['applicationId', $this->applicationId],
            ['step', $this->step],
        ];
    }

    private function makeRestCallMap()
    {
        $orgBundle = array(
            'children' => array(
                'licence' => array(
                    'children' => array('organisation')
                ),
            ),
        );

        $orgResult = [
            'licence' => [
                'organisation' => [
                    'id' => 1,
                ]
            ]
        ];

        $orgResultWithType = [
            'licence' => [
                'organisation' => [
                    'id' => 1,
                    'organisationType' => 'org_type.st',
                    'version' => 1,
                ]
            ]
        ];

        $companyFound = [
            'Count' => 1,
            'Results' => [
                [
                    'CompanyName' => 'SomeName'
                ]
            ]
        ];

        $companyNotFound = [
            'Count' => 0,
            'Results' => []
        ];

        return [
            ['Application', 'GET', array('id' => 1), $orgBundle, $orgResult],
            ['Application', 'GET', array('id' => 2), $orgBundle, $orgResultWithType],
            ['CompaniesHouse', 'GET', array('type' => 'numberSearch', 'value' => '01234567'), null, $companyFound],
            ['CompaniesHouse', 'GET', array('type' => 'numberSearch', 'value' => '00000000'), null, $companyNotFound]
        ];
    }
}
