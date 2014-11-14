<?php

/**
 * Operator business details controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Operator;

use OlcsTest\Controller\Operator\AbstractOperatorControllerTest;
use OlcsTest\Bootstrap;

/**
 * Operator controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class OperatorBusinessDetailsControllerTest extends AbstractOperatorControllerTest
{
    /**
     * @var array
     */
    protected $mockMethods = [
        'params',
        'getServiceLocator',
        'getRequest',
        'isButtonPressed',
        'redirectToRoute',
        'getResponse'
    ];

    /**
     * @var string
     */
    protected $request;

    /**
     * @var int
     */
    protected $statusCode = 200;

    /**
     * @var string
     */
    protected $controllerName = '\Olcs\Controller\Operator\OperatorBusinessDetailsController';

    /**
     * @var array
     */
    protected $post = [
        'operator-details' => [
            'id' => 1,
            'version' => 1,
            'name' => 'name'
        ],
        'form-actions' => ['save' => '']
    ];

    /**
     * Set up action
     */
    public function setUpAction($operator, $isPost = false, $isButtonCancelPressed = false)
    {
        $organisation = [
            'name' => 'name',
            'id' => 1,
            'version' => 1
        ];

        $mockOrganisation = $this->getMock(
            '\StdClass',
            ['getOrganisation', 'updateOrganisation', 'createOrganisation']
        );
        $mockOrganisation->expects($this->any())
            ->method('getOrganisation')
            ->with($this->equalTo(1), $this->equalTo(false))
            ->will($this->returnValue($organisation));

        $mockTranslator = $this->getMock('\StdClass', ['translate']);
        $mockTranslator->expects($this->any())
            ->method('translate')
            ->with($this->equalTo('internal-operator-create-new-operator'))
            ->will($this->returnValue('some translated text'));

        $mockParams = $this->getMock('\StdClass', ['fromRoute']);
        $mockParams->expects($this->any())
            ->method('fromRoute')
            ->with($this->equalTo('operator'))
            ->will($this->returnValue($operator));

        $mockResponse = $this->getMock('Zend\Http\Response', ['getStatusCode']);
        $mockResponse->expects($this->any())
            ->method('getStatusCode')
            ->will($this->returnValue($this->statusCode));

        $this->request = $this->getMock('\StdClas', ['isPost', 'getPost', 'getUri', 'isXmlHttpRequest']);

        $mockUri = $this->getMock('\StdClass', ['getPath']);
        $mockUri->expects($this->any())
            ->method('getPath')
            ->will($this->returnValue('/'));

        $this->request->expects($this->any())
            ->method('isPost')
            ->will($this->returnValue($isPost));

        $this->request->expects($this->any())
            ->method('getPost')
            ->will($this->returnValue($this->post));

        $this->request->expects($this->any())
            ->method('getUri')
            ->will($this->returnValue($mockUri));

        $this->request->expects($this->any())
            ->method('isXmlHttpRequest')
            ->will($this->returnValue(false));

        $this->controller->expects($this->any())
            ->method('getRequest')
            ->will($this->returnValue($this->request));

        $this->serviceManager = Bootstrap::getServiceManager();
        $this->serviceManager->setAllowOverride(true);
        $this->serviceManager->setService('Olcs\Service\Data\Organisation', $mockOrganisation);
        $this->serviceManager->setService('translator', $mockTranslator);

        $this->controller->expects($this->any())
            ->method('getResponse')
            ->will($this->returnValue($mockResponse));

        $this->controller->expects($this->any())
            ->method('isButtonPressed')
            ->with('cancel')
            ->will($this->returnValue($isButtonCancelPressed));

        $this->controller->expects($this->any())
            ->method('redirectToRoute')
            ->will($this->returnValue($mockResponse));

        $this->controller->expects($this->any())
            ->method('params')
            ->will($this->returnValue($mockParams));

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->will($this->returnValue($this->serviceManager));

        $this->controller->setEnabledCsrf(false);

    }

    /**
     * Test index action with edit operator
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithEditOperator()
    {
        $this->setUpAction(1);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test index action with add operator
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithAddOperator()
    {
        $this->setUpAction(null);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test index action with post add operator
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithPostAddOperator()
    {
        $this->statusCode = 302;
        $this->setUpAction(null, true);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with post edit operator
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithPostEditOperator()
    {
        $this->setUpAction(1, true);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test index action with add operator and cancel button pressed
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithAddOperatorAndCancelButtonPressed()
    {
        $this->setUpAction(null, true, true);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with edit operator and cancel button pressed
     *
     * @group operatorBusinessDetailsController
     */
    public function testIndexActionWithEditOperatorAndCancelButtonPressed()
    {
        $this->setUpAction(1, true, true);
        $response = $this->controller->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }
}
