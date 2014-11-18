<?php
namespace OlcsTest\Controller\Submission;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Zend\Http\Request;
use Zend\Http\Response;
use Olcs\TestHelpers\ControllerRouteMatchHelper;
use Olcs\TestHelpers\ControllerPluginManagerHelper;

/**
 * Submission controller form post tests
 *
 * @author Shaun Lizzio <shaun.lizzio@valtech.co.uk>
 */
class SubmissionControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(
            include __DIR__ . '/../../../../../../' . 'config/application.config.php'
        );
        $this->controller = $this->getMock(
            '\Olcs\Controller\Cases\Submission\SubmissionController', array(
                'getParams',
                'getFromPost',
                'getPersist',
                'setPersist',
                'getCase',
                'getForm',
                'generateFormWithData',
                'getDataForForm',
                'callParentProcessSave',
                'callParentSave',
                'callParentProcessLoad',
                'createSubmissionSection',
                'getServiceLocator',
                'saveThis'
            )
        );
        $serviceManager = Bootstrap::getServiceManager();
        $this->controller->setServiceLocator($serviceManager);
        $this->routeMatchHelper = new ControllerRouteMatchHelper();
        $this->controller->routeParams = array();

        parent::setUp();
    }

    public function testAddLoadsScripts()
    {
        $scriptMock = $this->getMock('\stdClass', ['loadFile']);
        $scriptMock->expects($this->once())
            ->method('loadFile')
            ->with('forms/submission');

        $sm = $this->getMock('\stdClass', ['get']);
        $sm->expects($this->once())
            ->method('get')
            ->with('Script')
            ->willReturn($scriptMock);

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($sm);

        $this->controller->expects($this->once())
            ->method('saveThis');

        $this->controller->addAction();
    }

    public function testEditLoadsScripts()
    {
        $scriptMock = $this->getMock('\stdClass', ['loadFile']);
        $scriptMock->expects($this->once())
            ->method('loadFile')
            ->with('forms/submission');

        $sm = $this->getMock('\stdClass', ['get']);
        $sm->expects($this->once())
            ->method('get')
            ->with('Script')
            ->willReturn($scriptMock);

        $this->controller->expects($this->any())
            ->method('getServiceLocator')
            ->willReturn($sm);

        $this->controller->expects($this->once())
            ->method('saveThis');

        $this->controller->editAction();
    }

    /**
     * Test process save of new submissions
     *
     * @param $dataToSave
     * @param $expectedResult
     *
     * @dataProvider getSubmissionSectionsToProcessSaveProvider
     */
    public function testProcessSaveAddNew($dataToSave, $expectedResult)
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
    public function testProcessLoad($dataToLoad)
    {
        $this->controller->expects($this->once())
            ->method('callParentProcessLoad')
            ->with($dataToLoad)
            ->will($this->returnValue($dataToLoad));

        $this->controller->expects($this->once())
            ->method('getCase')
            ->will($this->returnValue(['id' => 24]));

        $result = $this->controller->processLoad($dataToLoad);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('version', $result);
        $this->assertArrayHasKey('submissionSections', $result['fields']);
        $this->assertArrayHasKey('case', $result['fields']);

    }

    /**
     * Tests the first time a user goes to the submission form
     */
    public function testAlterFormBeforeValidationNoSubmissionType()
    {
        $mockForm = $this->getMock(
            '\Zend\Form\Form',
            array(
                'remove',
                'get'
            )
        );

        $mockForm->expects($this->once())
            ->method('remove')
            ->with($this->equalTo('form-actions'));

        $this->controller->alterFormBeforeValidation($mockForm);
    }

    /**
     * Tests the submission type being chosen
     */
    public function testAlterFormBeforeValidationSubmissionTypePosted()
    {
        $mockPostData = [
            'submissionSections' => [
                'submissionTypeSubmit' => 'some_type'
            ]
        ];
        $caseId = 24;
        $mockCase = ['id' => $caseId];

        $mockForm = $this->getMock(
            '\Zend\Form\Form'
        );
        $pluginManagerHelper = new ControllerPluginManagerHelper();
        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
            ]
        );
        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromPost')->with('fields')->andReturn($mockPostData);
        $mockParams->shouldReceive('fromRoute')->with('action');
        $mockParams->shouldReceive('fromRoute')->with('submission');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn(['id' => $caseId]);

        $mockCaseService = m::mock('Olcs\Service\Data\Cases');
        $mockCaseService->shouldReceive('fetchCaseData')->with($caseId)->andReturn($mockCase);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('DataServiceManager')->andReturnSelf();
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Cases')->andReturn($mockCaseService);
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);

        $sut = new \Olcs\Controller\Cases\Submission\SubmissionController();
        $sut->setServiceLocator($mockServiceManager);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->alterFormBeforeValidation($mockForm);
        $this->assertSame($mockForm, $result);
    }

    public function testInsertSubmission()
    {

        $data = ['submissionSections' =>
            [
                'submissionType' => 'bar',
                'sections' => [
                    0 => 'section1',
                    1 => 'section2'
                ]
            ]
        ];
        $service = 'Submission';

        $mockConfig = ['submission_config' =>
            [
                'sections' =>
                    [
                        'section1' => 'foo'
                    ]
            ]
        ];

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->withAnyArgs()->andReturn(['id' => 99]);

        $mockCommentService = m::mock('Olcs\Service\Data\SubmissionSectionComment');
        $mockCommentService->shouldReceive('generateComments')
            ->withAnyArgs()
            ->andReturn($this->generateMockComment());

        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('generateSnapshotData')
            ->withAnyArgs()
            ->andReturn(['sectionData']);
        $mockSubmissionService->shouldReceive('generateSnapshotData')
            ->withAnyArgs()
            ->andReturn(['sectionData']);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('config')->andReturn($mockConfig);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\SubmissionSectionComment')
            ->andReturn($mockCommentService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);

        $sut = new \Olcs\Controller\Cases\Submission\SubmissionController();
        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'submission'));
        $sut->setEvent($event);

        $sut->getEvent()->getRouteMatch()->setParam('case', 24);

        $sut->setServiceLocator($mockServiceManager);

        $result = $sut->save($data, $service);

        $this->assertArrayHasKey('id', $result);
        $this->assertArrayHasKey('dataSnapshot', $result);
        $this->assertArrayHasKey('submissionSections', $result);
        $this->assertArrayHasKey('submissionType', $result);
    }

    public function testRefreshTable()
    {
        $submissionId = 99;
        $caseId = 24;
        $section = 'persons';
        $mockConfig = ['submission_config'=>['sections' => [$section => 'foo']]];

        $submissionData = ['version' => 1, 'dataSnapshot' => '{"' . $section . '":{"data":"foo"}}'];

        $submissionSectionData = ['data' => 'bar'];
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );
        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockRestHelper = m::mock('RestHelper');

        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Submission',
            'PUT',
            [
                'id' => $submissionId,
                'version' => $submissionData['version'],
                'dataSnapshot' => json_encode([$section => ['data' => $submissionSectionData]])
            ],
            ''
        )->andReturnNull();

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('submission')->andReturn($submissionId);
        $mockParams->shouldReceive('fromRoute')->with('section')->andReturn($section);

        $mockSubmissionService->shouldReceive('fetchData')
            ->with($submissionId)
            ->andReturn($submissionData);
        $mockSubmissionService->shouldReceive('createSubmissionSection')
            ->with($caseId, $section, $mockConfig['submission_config']['sections'][$section])
            ->andReturn($submissionSectionData);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('config')->andReturn($mockConfig);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);

        $sut = new \Olcs\Controller\Cases\Submission\SubmissionController();

        $sut->setServiceLocator($mockServiceManager);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->refreshTable();

        $this->assertNull($result);
    }

    public function testDeleteTableRows()
    {
        $submissionId = 99;
        $caseId = 24;
        $section = 'persons';
        $mockConfig = ['submission_config'=>['sections' => [$section => 'foo']]];

        $submissionData = ['version' => 1, 'dataSnapshot' => '{"' . $section . '":{"data":{"0":{"id":"77"}}}}'];

        $submissionSectionData = ['data' => 'bar'];
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );

        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockRestHelper = m::mock('RestHelper');

        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Submission',
            'PUT',
            [
                'id' => $submissionId,
                'version' => $submissionData['version'],
                'dataSnapshot' => json_encode([$section => ['data' => []]])
            ],
            ''
        )->andReturnNull();

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromPost')->with('id')->andReturn([0 => 77]);
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('submission')->andReturn($submissionId);
        $mockParams->shouldReceive('fromRoute')->with('section')->andReturn($section);

        $mockSubmissionService->shouldReceive('fetchData')
            ->with($submissionId)
            ->andReturn($submissionData);
        $mockSubmissionService->shouldReceive('createSubmissionSection')
            ->with($caseId, $section, $mockConfig['submission_config']['sections'][$section])
            ->andReturn($submissionSectionData);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('config')->andReturn($mockConfig);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);

        $sut = new \Olcs\Controller\Cases\Submission\SubmissionController();

        $event = $this->routeMatchHelper->getMockRouteMatch(
            array('controller' => 'submission_updateTable','action' => 'update-table')
        );
        $sut->setEvent($event);

        $sut->getEvent()->getRouteMatch()->setParam('case', $caseId);
        $sut->getEvent()->getRouteMatch()->setParam('submission', $submissionId);
        $sut->getEvent()->getRouteMatch()->setParam('section', $section);

        $sut->setServiceLocator($mockServiceManager);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->deleteTableRows();

        $this->assertNull($result);
    }

    public function testUpdateTableDeleteRows()
    {
        $submissionId = 99;
        $caseId = 24;
        $section = 'persons';
        $mockConfig = ['submission_config'=>['sections' => [$section => 'foo']]];

        $submissionData = ['version' => 1, 'dataSnapshot' => '{"' . $section . '":{"data":"foo"}}'];

        $submissionSectionData = ['data' => 'bar'];
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );
        $mockResponse = m::mock('\Zend\Http\Response');
        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('fetchData')
            ->with($submissionId)
            ->andReturn($submissionData);
        $mockSubmissionService->shouldReceive('createSubmissionSection')
            ->with($caseId, $section, $mockConfig['submission_config']['sections'][$section])
            ->andReturn($submissionSectionData);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Submission',
            'PUT',
            [
                'id' => $submissionId,
                'version' => $submissionData['version'],
                'dataSnapshot' => json_encode([$section => ['data' => []]])
            ],
            ''
        )->andReturnNull();

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('submission')->andReturn($submissionId);
        $mockParams->shouldReceive('fromRoute')->with('section')->andReturn($section);
        $mockParams->shouldReceive('fromPost')->with('formAction')->andReturn('delete-row');
        $mockParams->shouldReceive('fromPost')->with('id')->andReturn([0 => 77]);

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'submission',
            ['action' => 'details', 'submission' => $submissionId],
            [],
            true
        )->andReturn($mockResponse);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('config')->andReturn($mockConfig);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);
        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $sut = new \Olcs\Controller\Cases\Submission\SubmissionController();

        $event = $this->routeMatchHelper->getMockRouteMatch(
            array('controller' => 'submission','action' => 'refresh')
        );
        $sut->setEvent($event);

        $sut->getEvent()->getRouteMatch()->setParam('case', $caseId);
        $sut->getEvent()->getRouteMatch()->setParam('submission', $submissionId);
        $sut->getEvent()->getRouteMatch()->setParam('section', $section);

        $sut->setServiceLocator($mockServiceManager);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->updateTableAction();

        $this->assertEquals($result, $mockResponse);
    }


    public function testUpdateTableRefreshTable()
    {
        $submissionId = 99;
        $caseId = 24;
        $section = 'persons';
        $mockConfig = ['submission_config'=>['sections' => [$section => 'foo']]];

        $submissionData = ['version' => 1, 'dataSnapshot' => '{"' . $section . '":{"data":"foo"}}'];

        $submissionSectionData = ['data' => 'bar'];
        $pluginManagerHelper = new ControllerPluginManagerHelper();

        $mockPluginManager = $pluginManagerHelper->getMockPluginManager(
            [
                'params' => 'Params',
                'redirect' => 'Redirect'
            ]
        );
        $mockResponse = m::mock('\Zend\Http\Response');
        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('fetchData')
            ->with($submissionId)
            ->andReturn($submissionData);
        $mockSubmissionService->shouldReceive('createSubmissionSection')
            ->with($caseId, $section, $mockConfig['submission_config']['sections'][$section])
            ->andReturn($submissionSectionData);

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Submission',
            'PUT',
            [
                'id' => $submissionId,
                'version' => $submissionData['version'],
                'dataSnapshot' => json_encode([$section => ['data' => ['data' => 'bar']]])
            ],
            ""
        )->andReturnNull();

        $mockParams = $mockPluginManager->get('params', '');
        $mockParams->shouldReceive('fromRoute')->with('case')->andReturn($caseId);
        $mockParams->shouldReceive('fromRoute')->with('submission')->andReturn($submissionId);
        $mockParams->shouldReceive('fromRoute')->with('section')->andReturn($section);
        $mockParams->shouldReceive('fromPost')->with('formAction')->andReturn('refresh-table');

        $mockRedirect = $mockPluginManager->get('redirect', '');
        $mockRedirect->shouldReceive('toRoute')->with(
            'submission',
            ['action' => 'details', 'submission' => $submissionId],
            [],
            true
        )->andReturn($mockResponse);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('config')->andReturn($mockConfig);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);
        $mockPluginManager->shouldReceive('get')->with('redirect')->andReturn($mockRedirect);

        $sut = new \Olcs\Controller\Cases\Submission\SubmissionController();

        $event = $this->routeMatchHelper->getMockRouteMatch(
            array('controller' => 'submission','action' => 'refresh')
        );
        $sut->setEvent($event);

        $sut->getEvent()->getRouteMatch()->setParam('case', $caseId);
        $sut->getEvent()->getRouteMatch()->setParam('submission', $submissionId);
        $sut->getEvent()->getRouteMatch()->setParam('section', $section);

        $sut->setServiceLocator($mockServiceManager);
        $sut->setPluginManager($mockPluginManager);

        $result = $sut->updateTableAction();

        $this->assertEquals($result, $mockResponse);
    }

    public function testUpdateSubmission()
    {

        $data = [
            'id' => 3,
            'submissionSections' =>
            [
                'submissionType' => 'bar',
                'sections' => [
                    0 => 'section1',
                    1 => 'section2'
                ]
            ]
        ];
        $service = 'Submission';

        $mockConfig = ['submission_config' =>
            [
                'sections' =>
                    [
                        'section1' => 'foo'
                    ]
            ]
        ];

        $mockRestHelper = m::mock('RestHelper');
        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'Submission',
            'PUT',
            m::type('array'),
            ''
        )->andReturnNull();

        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'SubmissionSectionComment',
            'POST',
            m::type('array'),
            ''
        );

        $mockRestHelper->shouldReceive('makeRestCall')->with(
            'SubmissionSectionComment',
            'DELETE',
            m::type('array'),
            ''
        );

        $mockCommentService = m::mock('Olcs\Service\Data\SubmissionSectionComment');
        $mockCommentService->shouldReceive('updateComments')
            ->withAnyArgs()
            ->andReturn(['add' => $this->generateMockComment(1), 'remove' => $this->generateMockComment(2)]);

        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('generateSnapshotData')
            ->withAnyArgs()
            ->andReturn(['sectionData']);
        $mockSubmissionService->shouldReceive('generateSnapshotData')
            ->withAnyArgs()
            ->andReturn(['sectionData']);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');
        $mockServiceManager->shouldReceive('get')->with('Helper\Rest')->andReturn($mockRestHelper);
        $mockServiceManager->shouldReceive('get')->with('config')->andReturn($mockConfig);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\SubmissionSectionComment')
            ->andReturn($mockCommentService);
        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);

        $sut = new \Olcs\Controller\Cases\Submission\SubmissionController();
        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'submission'));
        $sut->setEvent($event);

        $sut->getEvent()->getRouteMatch()->setParam('case', 24);

        $sut->setServiceLocator($mockServiceManager);

        $result = $sut->save($data, $service);

        $this->assertArrayHasKey('dataSnapshot', $result);
        $this->assertArrayHasKey('submissionSections', $result);
        $this->assertArrayHasKey('submissionType', $result);
    }

    public function testDetailsAction()
    {
        $sut = new \Olcs\Controller\Cases\Submission\SubmissionController();

        $submissionId = 99;
        $mockSubmission = [
            'id' => $submissionId,
            'submissionType' =>
            [
                'id' => 'foo'
            ]
        ];

        $mockSelectedSectionArray = [
            0 => [
                'sectionId' => 'section1',
                'data' => []
            ]
        ];

        $mockConfig = ['submission_config' =>
            [
                'sections' =>
                    [
                        'section1' => 'foo'
                    ]
            ]
        ];

        $mockSubmissionTitle = 'Section title';
        $placeholder = new \Zend\View\Helper\Placeholder();

        $mockViewHelperManager = new \Zend\View\HelperPluginManager();
        $mockViewHelperManager->setService('placeholder', $placeholder);

        $event = $this->routeMatchHelper->getMockRouteMatch(array('controller' => 'submission_section_comment'));
        $sut->setEvent($event);

        $sut->getEvent()->getRouteMatch()->setParam('submission', $submissionId);

        $mockSubmissionService = m::mock('Olcs\Service\Data\Submission');
        $mockSubmissionService->shouldReceive('fetchData')
            ->with($submissionId)
            ->andReturn($mockSubmission);
        $mockSubmissionService->shouldReceive('getSubmissionTypeTitle')
            ->with($mockSubmission['submissionType']['id'])
            ->andReturn($mockSubmissionTitle);
        $mockSubmissionService->shouldReceive('extractSelectedSubmissionSectionsData')
            ->with(array_merge($mockSubmission, ['submissionTypeTitle' => $mockSubmissionTitle]))
            ->andReturn($mockSelectedSectionArray);
        $mockSubmissionService->shouldReceive('getAllSectionsRefData')
            ->andReturn($mockSelectedSectionArray);
        $mockSubmissionService->shouldReceive('canReopen')
            ->andReturn(false);
        $mockSubmissionService->shouldReceive('canClose')
            ->andReturn(false);

        $mockServiceManager = m::mock('\Zend\ServiceManager\ServiceManager');

        $mockServiceManager->shouldReceive('get')->with('config')->andReturn($mockConfig);

        $mockServiceManager->shouldReceive('get')->with('Olcs\Service\Data\Submission')
            ->andReturn($mockSubmissionService);

        $mockServiceManager->shouldReceive('get')->with('viewHelperManager')
            ->andReturn($mockViewHelperManager);

        $sut->setServiceLocator($mockServiceManager);

        $sut->detailsAction();

        $this->assertEquals(
            $mockSelectedSectionArray,
            $mockViewHelperManager->get('placeholder')->getContainer('selectedSectionsArray')->getValue()
        );
        $this->assertEquals(
            array_merge($mockSubmission, ['submissionTypeTitle' => $mockSubmissionTitle]),
            $mockViewHelperManager->get('placeholder')->getContainer('submission')->getValue()
        );
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

    public function getSubmissionSectionsToProcessSaveProvider()
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
                    'submissionSections' => [
                        'sections' => '[{"sectionId":"introduction"}]'
                    ],
                )
            ),
            array(
                array(
                    'id' => 2,
                    'version' => 1,
                    'submissionType' => 'foo',
                    'dataSnapshot' => '[{"case-summary":{"data":[]}}]'
                )
            )
        );
    }

    private function generateMockComment($id = '')
    {
        return [
            0 => [
                'id' => $id,
                'submissionSection' => 'foo',
                'comment' => 'bar'
            ]
        ];
    }
}
