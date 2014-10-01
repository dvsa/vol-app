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
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
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
                'getCase',
                'getForm',
                'generateFormWithData',
                'getDataForForm',
                'callParentProcessSave',
                'callParentProcessLoad'
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
     * Tests first request for edit Action
     */
    public function testEditAction()
    {

        $formData = ['submissionSections' => ['submissionTypeSubmit' => '']];

        $this->controller->expects($this->once())
            ->method('setPersist')
            ->with(false);

        $this->controller->expects($this->once())
            ->method('getFromPost')
            ->with('fields')
            ->will($this->returnValue($formData));

        $mockForm = $this->getMock('\stdClass', ['remove']);

        $this->controller->expects($this->once())
            ->method('generateFormWithData')
            ->will($this->returnValue($mockForm));

        $response = $this->controller->editAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Tests edit request when changing submission type
     */
    public function testEditActionChangingSubmissionType()
    {

        $formData = ['submissionSections' => []];

        $this->controller->expects($this->once())
            ->method('getFromPost')
            ->with('fields')
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

        $response = $this->controller->editAction();

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

    /**
     * Test processLoad of submissions
     *
     * @param $dataToLoad
     * @param $loadedData
     *
     * @dataProvider getSubmissionSectionsToLoadProvider
     */
    public function testProcessLoad($dataToLoad, $loadedData)
    {
        $this->controller->expects($this->once())
            ->method('callParentProcessLoad')
            ->with($dataToLoad)
            ->will($this->returnValue($dataToLoad));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(['id' => 24]));

        $result = $this->controller->processLoad($dataToLoad);

        $this->assertEquals($result, $loadedData);

    }

    /**
     * Test GetSubmissionTypeTitle
     *
     * @param $input
     * @param $expectedResult
     *
     * @dataProvider getSubmissionTitlesProvider
     */
    public function testGetSubmissionTypeTitle($input, $expectedResult)
    {
        $result = $this->controller->getSubmissionTypeTitle($input['submissionTypeId'], $input['submissionTitles']);

        $this->assertEquals($result, $expectedResult);
    }

    public function getSubmissionTitlesProvider()
    {
        return array(
            array(
                array(
                    'submissionTypeId' => 'submission_type_o_test',
                    'submissionTitles' => array(
                        array(
                            'id' => 'submission_type_t_test',
                            'description' => 'test title'
                        )
                    )
                ),
                'test title'
            ),
            array(
                array(
                    'submissionTypeId' => 'submission_type_o_testdoesntexist',
                    'submissionTitles' => array(
                        array(
                            'id' => 'submission_type_t_test',
                            'description' => 'test title'
                        )
                    )
                ),
                ''
            )
        );

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

    public function getSubmissionSectionsToLoadProvider()
    {
        return array(
            array(
                array(
                    'id' => 1,
                    'version' => 1,
                    'submissionType' => 'foo',
                    'text' => '[{"sectionId":"submission_section_casu","data":{"data":[]}}]'
                ),
                array(
                    'id' => 1,
                    'version' => 1,
                    'submissionType' => 'foo',
                    'fields' => [
                        'case' => 24,
                        'submissionSections' => [
                            'submissionType' => 'foo',
                            'sections' => ['submission_section_casu']
                        ],
                        'id' => 1,
                        'version' => 1,
                    ],
                    'text' => '[{"sectionId":"submission_section_casu","data":{"data":[]}}]',
                    'case' => 24
                ),
            ),
            array(
                array(
                    'id' => 1,
                    'version' => 1,
                    'submissionSections' => [
                        'sections' => '[{"sectionId":"submission_section_casu"}]',
                    ],
                ),
                array(
                    'id' => 1,
                    'version' => 1,
                    'fields' => [
                        'case' => 24,
                        'submissionSections' => [
                            'sections' => ['submission_section_casu']
                        ],
                    ],
                    'submissionSections' => [
                        'sections' => '[{"sectionId":"submission_section_casu"}]'
                    ],
                )
            )
        );

    }

}
