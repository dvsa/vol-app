<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;

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
            '\Olcs\Controller\Cases\Submission\SubmissionController', array(
                'getFromPost',
                'getPersist',
                'setPersist',
                'getForm',
                'generateFormWithData',
                'getDataForForm',
            )
        );
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller->setServiceLocator($serviceManager);

        $this->controller->routeParams = array();

        parent::setUp();
    }

    /**
     * Tests first request for add Action
     */
    public function testAddAction()
    {

        $formData = [];

        $this->controller->expects($this->once())
            ->method('getFromPost')
            ->with('fields')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getDataForForm')
            ->will($this->returnValue($formData));


        $mockForm = $this->getMock('\stdClass', ['remove']);

        $mockForm->expects($this->once())
            ->method('remove')
            ->with('formActions[submit]');

        $this->controller->expects($this->once())
            ->method('getForm')
            ->with('submission')
            ->will($this->returnValue($mockForm));

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($mockForm));

        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }


    public function testAddActionSubmissionTypeSet()
    {

        $formData = ['submissionSections' => ['submissionTypeSubmit' => '']];

        $this->controller->expects($this->once())
            ->method('getFromPost')
            ->with('fields')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('getDataForForm')
            ->will($this->returnValue($formData));

        $this->controller->expects($this->once())
            ->method('setPersist')
            ->with(false);

        $mockForm = $this->getMock('\stdClass', ['remove']);

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($mockForm));

        $response = $this->controller->addAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }
}
