<?php

/**
 * Search controller form post tests
 *
 * @author adminmwc <michael.cooper@valtech.co.uk>
 */
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;

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
                'callParentProcessSave'
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


    /**
     * Tests the addAction once a submissionType has been set.
     */
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

    /**
     * Test save of new submissions
     *
     * @param $dataToSave
     * @param $expectedResult
     *
     * @dataProvider getSubmissionSectionsToSaveProvider
     */
    public function testSaveAddNew($dataToSave, $expectedResult)
    {
        $this->controller->expects($this->once())
            ->method('callParentProcessSave')
            ->with($dataToSave)
            ->will($this->returnValue($expectedResult));

        $mockResponse = m::mock('\Zend\Http\Response');

        $mockRedirectPlugin = m::mock('\Zend\Controller\Plugin\Redirect');
        $mockRedirectPlugin->shouldReceive('toRoute')->with(
            'submission',
            ['action' => 'details', 'submission' => $expectedResult['id']],
            [],
            true
        )->andReturn($mockResponse);

        $mockControllerPluginManager = m::mock('\Zend\Mvc\Controller\PluginManager');
        $mockControllerPluginManager->shouldReceive('setController')->withAnyArgs();
        $mockControllerPluginManager->shouldReceive('get')->with('redirect', '')->andReturn($mockRedirectPlugin);

        $this->controller->setPluginManager($mockControllerPluginManager);

        $this->controller->processSave($dataToSave);
    }

    public function getSubmissionSectionsToSaveProvider()
    {
        return array(
            array(
                array(
                    'fields' =>
                        array(
                            'submissionSections[submissionType]' => 'sub type 1',
                            'submissionSections[sections]' => ['section1', 'section2']
                        )
                ),
                array(
                    'id' => 1
                )
            )
        );

    }
}
