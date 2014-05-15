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

    public function testBusinessTypeWithNoTypePersisted()
    {
        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'generateSectionForm',
            'getPersistedFormData'
            )
        );

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
            ->method('generateSectionForm')
            ->will($this->returnValue($this->getMock('\Zend\Form\Form')));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));

        $this->controller->generateStepFormAction();
    }

    public function testSoleTraderWithTypePersisted()
    {
        $this->step = 'sole-trader';
        $this->applicationId = 2;

        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'generateSectionForm',
            'getPersistedFormData'
            )
        );

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
            ->method('generateSectionForm')
            ->will($this->returnValue($this->getMock('\Zend\Form\Form')));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));

        $this->controller->generateStepFormAction();
    }

    public function testStepWithNoTypePersisted()
    {
        $this->step = 'sole-trader';
        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'redirect'
            )
        );

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

    public function testDetailsActionWithNoTypePersisted()
    {
        $this->step = 'sole-trader';
        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'redirect'
            )
        );

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

        $action = $this->controller->detailsAction();
        $this->assertInstanceOf('\Zend\Http\Response', $action);
    }

    public function testDetailsActionWithTypePersisted()
    {
        $this->step = 'sole-trader';
        $this->applicationId = 2;

        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'redirect',
            'generateSectionForm',
            'forward',
            )
        );

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

        $fieldsetMock = $this->getMock('\stdClass', array('getOptions'));
        $fieldsetMock->expects($this->once())
            ->method('getOptions')
            ->will($this->returnValue(['next_step' => ['values' => ['org_type.st']]]));

        $mockForm = $this->getMock('\Zend\Form\Form', array('get'));
        $mockForm->expects($this->once())
            ->method('get')
            ->will($this->returnValue($fieldsetMock));

        $this->controller->expects($this->once())
            ->method('generateSectionForm')
            ->will($this->returnValue($mockForm));

        $mockForward = $this->getMock('\stdClass', array('dispatch'));
        $mockForward->expects($this->once())
            ->method('dispatch')
            ->will($this->returnValue($responseMock));

        $this->controller->expects($this->once())
            ->method('forward')
            ->will($this->returnValue($mockForward));

        $action = $this->controller->detailsAction();
        $this->assertInstanceOf('\Zend\Http\Response', $action);
    }

    public function testRegisteredCompanyFound()
    {
        $this->step = 'registered-company';
        $this->applicationId = 2;

        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'generateSectionForm',
            'getPersistedFormData',
            'determineSubmitButtonPressed',
            'getRequest'
             )
        );

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

        $mockElement = $this->getMock('\Zend\Form\Element');
        $mockElement->expects($this->any())
                ->method('setValue')
                ->will($this->returnValue(true));

        $mockFieldset = $this->getMock('\Zend\Form\Fieldset');
        $mockFieldset->expects($this->any())
                ->method('get')
                ->will($this->returnValue($mockElement));

        $mockForm = $this->getMock('\Zend\Form\Form');
        $mockForm->expects($this->any())
                ->method('isValid')
                ->will($this->returnValue(true));

        $mockForm->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(array('registered-company' => array('company_number' => '01234567'))));

        $mockForm->expects($this->once())
                ->method('get')
                ->with('registered-company')
                ->will($this->returnValue($mockFieldset));

        $this->controller->expects($this->once())
            ->method('generateSectionForm')
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('determineSubmitButtonPressed')
            ->will($this->returnValue('lookup_company'));

        $mockRequest = $this->getMock('\Zend\Http\Request', array('isPost'));
        $mockRequest->expects($this->once())
                ->method('isPost')
                ->will($this->returnValue(true));

        $this->controller->expects($this->any())
                ->method('getRequest')
                ->will($this->returnValue($mockRequest));

        $this->controller->generateStepFormAction();
    }

    public function testRegisteredCompanyNotFound()
    {
        $this->step = 'registered-company';
        $this->applicationId = 2;

        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'generateSectionForm',
            'getPersistedFormData',
            'determineSubmitButtonPressed',
            'getRequest'
            )
        );

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

        $mockElement = $this->getMock('\Zend\Form\Element');
        $mockElement->expects($this->any())
                ->method('setValue')
                ->will($this->returnValue(true));

        $mockFieldset = $this->getMock('\Zend\Form\Fieldset');
        $mockFieldset->expects($this->any())
                ->method('get')
                ->will($this->returnValue($mockElement));

        $mockForm = $this->getMock('\Zend\Form\Form');
        $mockForm->expects($this->any())
                ->method('isValid')
                ->will($this->returnValue(true));

        $mockForm->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(array('registered-company' => array('company_number' => '00000000'))));

        $mockForm->expects($this->once())
                ->method('get')
                ->with('registered-company')
                ->will($this->returnValue($mockFieldset));

        $this->controller->expects($this->once())
            ->method('generateSectionForm')
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('determineSubmitButtonPressed')
            ->will($this->returnValue('lookup_company'));

        $mockRequest = $this->getMock('\Zend\Http\Request', array('isPost'));
        $mockRequest->expects($this->once())
                ->method('isPost')
                ->will($this->returnValue(true));

        $this->controller->expects($this->any())
                ->method('getRequest')
                ->will($this->returnValue($mockRequest));

        $this->controller->generateStepFormAction();
    }

    /**
     * @group acurrent
     */
    public function testLlpFound()
    {
        $this->step = 'llp';
        $this->applicationId = 2;

        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'generateSectionForm',
            'getPersistedFormData',
            'determineSubmitButtonPressed',
            'getRequest'
             )
        );

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

        $mockElement = $this->getMock('\Zend\Form\Element');
        $mockElement->expects($this->any())
                ->method('setValue')
                ->will($this->returnValue(true));

        $mockFieldset = $this->getMock('\Zend\Form\Fieldset');
        $mockFieldset->expects($this->any())
                ->method('get')
                ->will($this->returnValue($mockElement));

        $mockForm = $this->getMock('\Zend\Form\Form');
        $mockForm->expects($this->any())
                ->method('isValid')
                ->will($this->returnValue(true));

        $mockForm->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(array('llp' => array('company_number' => '00000000'))));

        $mockForm->expects($this->once())
                ->method('get')
                ->with('llp')
                ->will($this->returnValue($mockFieldset));

        $this->controller->expects($this->once())
            ->method('generateSectionForm')
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('determineSubmitButtonPressed')
            ->will($this->returnValue('lookup_company'));

        $mockRequest = $this->getMock('\Zend\Http\Request', array('isPost'));
        $mockRequest->expects($this->once())
                ->method('isPost')
                ->will($this->returnValue(true));

        $this->controller->expects($this->any())
                ->method('getRequest')
                ->will($this->returnValue($mockRequest));

        $this->controller->generateStepFormAction();
    }

    /**
     * @group acurrent
     */
    public function testWrongCompanyLookupTypeFound()
    {
        $this->step = 'llp';
        $this->applicationId = 2;

        $this->setUpMockController(
            array(
            'params',
            'makeRestCall',
            'generateSectionForm',
            'getPersistedFormData',
            'determineSubmitButtonPressed',
            'getRequest'
            )
        );

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

        $mockElement = $this->getMock('\Zend\Form\Element');
        $mockElement->expects($this->any())
                ->method('setValue')
                ->will($this->returnValue(true));

        $mockFieldset = $this->getMock('\Zend\Form\Fieldset');
        $mockFieldset->expects($this->any())
                ->method('get')
                ->will($this->returnValue($mockElement));

        $mockForm = $this->getMock('\Zend\Form\Form');
        $mockForm->expects($this->any())
                ->method('isValid')
                ->will($this->returnValue(true));

        $mockForm->expects($this->once())
                ->method('getData')
                ->will($this->returnValue(array('wrong' => array('company_number' => '00000000'))));

        $this->controller->expects($this->once())
            ->method('generateSectionForm')
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('getPersistedFormData')
            ->will($this->returnValue(array()));

        $this->controller->expects($this->once())
            ->method('determineSubmitButtonPressed')
            ->will($this->returnValue('lookup_company'));

        $mockRequest = $this->getMock('\Zend\Http\Request', array('isPost'));
        $mockRequest->expects($this->once())
                ->method('isPost')
                ->will($this->returnValue(true));

        $this->controller->expects($this->any())
                ->method('getRequest')
                ->will($this->returnValue($mockRequest));

        $this->controller->generateStepFormAction();
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
