<?php

/**
 * Transport manager details competence controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\TransportManager\Details;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Common\Service\Entity\TmQualificationEntityService;
use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;

/**
 * Transport manager details competence controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsCompetenceControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    /**
     * @var array
     */
    protected $post = [
        'action' => 'Edit',
        'id' => [1],
        'js-submit' => 1,
        'table' => 'default'
    ];

    protected $tmDetails = [
    ];

    /**
     * Set up action
     */
    public function setUpAction()
    {
        $this->sut = m::mock('\Olcs\Controller\TransportManager\Details\TransportManagerDetailsCompetenceController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test index action
     * 
     * @group tmCompetences
     */
    public function testIndexAction()
    {
        $this->setUpAction();

        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(false)
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(false)
            ->getMock();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-competence')
            ->shouldReceive('setTerminal')
            ->with(false)
            ->getMock();

        $mockTable = m::mock()
            ->shouldReceive('render')
            ->andReturn('table')
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.qualifications', 'qualifications')
            ->andReturn($mockTable)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('loadScripts')
            ->with(['table-actions'])
            ->shouldReceive('getViewWithTm')
            ->with(['table' => 'table', 'form' => 'form'])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $this->mockUploadFiles($mockRequest);

        $mockTmQualification = m::mock()
            ->shouldReceive('getQualificationsForTm')
            ->with(1)
            ->andReturn('qualifications')
            ->getMock();

        $this->sm->setService('Entity\TmQualification', $mockTmQualification);

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Mock upload files funcitonality
     * 
     * @param Request $mockRequest
     */
    public function mockUploadFiles($mockRequest)
    {
        $this->sut
            ->shouldReceive('getForm')
            ->with('certificate-upload')
            ->andReturn('form');

        $mockFileUpload = m::mock()
            ->shouldReceive('setForm')
            ->with('form')
            ->andReturnSelf()
            ->shouldReceive('setSelector')
            ->with('file')
            ->andReturnSelf()
            ->shouldReceive('setUploadCallback')
            ->andReturnSelf()
            ->shouldReceive('setDeleteCallback')
            ->andReturnSelf()
            ->shouldReceive('setLoadCallback')
            ->andReturnSelf()
            ->shouldReceive('setRequest')
            ->with($mockRequest)
            ->shouldReceive('process')
            ->getMock();

        $this->sm->setService('Helper\FileUpload', $mockFileUpload);

    }

    /**
     * Test index action with edit button pressed
     * 
     * @group tmCompetences
     */
    public function testIndexActionWithEditButtonPressed()
    {
        $this->setUpAction();

        $mockTable = m::mock()
            ->shouldReceive('render')
            ->andReturn('table')
            ->getMock();

        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($this->post)
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(false)
            ->getMock();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-competence')
            ->shouldReceive('setTerminal')
            ->with(false)
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.qualifications', 'qualifications')
            ->andReturn($mockTable)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromPost')
                ->with('action')
                ->andReturn('Edit')
                ->shouldReceive('fromPost')
                ->with('id')
                ->andReturn(1)
                ->getMock()
            )
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRoute')
                ->with(null, ['action' => 'edit', 'id' => 1], [], true)
                ->andReturn('redirect')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['table-actions'])
            ->shouldReceive('getViewWithTm')
            ->with(['table' => 'table', 'form' => 'form'])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn('redirect');

        $mockTmQualification = m::mock()
            ->shouldReceive('getQualificationsForTm')
            ->with(1)
            ->andReturn('qualifications')
            ->getMock();

        $this->sm->setService('Entity\TmQualification', $mockTmQualification);
        $this->mockUploadFiles($mockRequest);

        $response = $this->sut->indexAction();
        $this->assertEquals('redirect', $response);
    }

    /**
     * Test index action with add button pressed
     * 
     * @group tmCompetences
     */
    public function testIndexActionWithAddButtonPressed()
    {
        $this->setUpAction();

        $mockTable = m::mock()
            ->shouldReceive('render')
            ->andReturn('table')
            ->getMock();

        $mockRequest = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(true)
            ->shouldReceive('getPost')
            ->andReturn($this->post)
            ->shouldReceive('isXmlHttpRequest')
            ->andReturn(false)
            ->getMock();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-competence')
            ->shouldReceive('setTerminal')
            ->with(false)
            ->getMock();

        $this->sut
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.qualifications', 'qualifications')
            ->andReturn($mockTable)
            ->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromPost')
                ->with('action')
                ->andReturn('Add')
                ->getMock()
            )
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRoute')
                ->with(null, ['action' => 'add'], [], true)
                ->andReturn('redirect')
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['table-actions'])
            ->shouldReceive('getViewWithTm')
            ->with(['table' => 'table', 'form' => 'form'])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn('redirect');

        $mockTmQualification = m::mock()
            ->shouldReceive('getQualificationsForTm')
            ->with(1)
            ->andReturn('qualifications')
            ->getMock();

        $this->sm->setService('Entity\TmQualification', $mockTmQualification);
        $this->mockUploadFiles($mockRequest);

        $response = $this->sut->indexAction();
        $this->assertEquals('redirect', $response);
    }

    /**
     * Test get delete service name
     * 
     * @group tmCompetences
     */
    public function testGetDeleteServiceName()
    {
        $this->setUpAction();

        $response = $this->sut->getDeleteServiceName();
        $this->assertEquals('TmQualification', $response);
    }

    /**
     * Test edit action
     * 
     * @group tmCompetences
     */
    public function testEditAction()
    {
        $this->setUpAction();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with(
                [
                    'qualification-details' => [
                        'id' => 1,
                        'version' => 1,
                        'issuedDate' => '2014-01-01',
                        'serialNo' => '123',
                        'qualificationType' => 'AR',
                        'countryCode' => 'GB'
                    ]
                ]
            )
            ->shouldReceive('remove')
            ->with('csrf')
            ->getMock();

        $mockTmQualification = m::mock()
            ->shouldReceive('getQualification')
            ->with(1)
            ->andReturn(
                [
                    'id' => 1,
                    'version' => 1,
                    'issuedDate' => '2014-01-01',
                    'serialNo' => '123',
                    'qualificationType' => ['id' => 'AR'],
                    'countryCode' => ['id' => 'GB']
                ]
            )
            ->getMock();

        $this->sm->setService('Entity\TmQualification', $mockTmQualification);

        $this->sut
            ->shouldReceive('getForm')
            ->with('qualification')
            ->andReturn($mockForm)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('renderView')
            ->andReturn(new ViewModel());

        $response = $this->sut->editAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test add action
     * 
     * @group tmCompetences
     */
    public function testAddAction()
    {
        $this->setUpAction();

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with(
                [
                    'qualification-details' => [
                        'countryCode' => 'GB'
                    ]
                ]
            )
            ->shouldReceive('remove')
            ->with('csrf')
            ->getMock();

        $this->sut
            ->shouldReceive('getForm')
            ->with('qualification')
            ->andReturn($mockForm)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(null)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('renderView')
            ->andReturn(new ViewModel());

        $response = $this->sut->addAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test edit action with save
     * 
     * @group tmCompetences
     */
    public function testEditActionWitSave()
    {
        $this->setUpAction();

        $qualificationDetails = [
            'qualification-details' => [
                'id' => 1,
                'version' => 1,
                'issuedDate' => '2014-01-01',
                'serialNo' => '123',
                'qualificationType' => 'AR',
                'countryCode' => 'GB'
            ]
        ];

        $postEditForm = array_merge($qualificationDetails, ['form-actions' => ['save' => []]]);

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($qualificationDetails)
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($qualificationDetails)
            ->shouldReceive('setData')
            ->with(array_merge($qualificationDetails, ['form-actions' => ['save' => []]]))
            ->getMock();

        $mockTmQualification = m::mock()
            ->shouldReceive('getQualification')
            ->with(1)
            ->andReturn(
                [
                    'id' => 1,
                    'version' => 1,
                    'issuedDate' => '2014-01-01',
                    'serialNo' => '123',
                    'qualificationType' => ['id' => 'AR'],
                    'countryCode' => ['id' => 'GB']
                ]
            )
            ->shouldReceive('save')
            ->with(array_merge($qualificationDetails['qualification-details'], ['transportManager' => 1]))
            ->getMock();

        $this->sm->setService('Entity\TmQualification', $mockTmQualification);

        $this->sut
            ->shouldReceive('getForm')
            ->with('qualification')
            ->andReturn($mockForm)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($postEditForm)
                ->getMock()
            )
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRouteAjax')
                ->with(null, ['transportManager' => 1])
                ->andReturn('redirect')
                ->getMock()
            )
            ->shouldReceive('getPersist')
            ->andReturn(true)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('content')
                ->getMock()
            );

        $response = $this->sut->editAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test edit action with cancel button pressed
     * 
     * @group tmCompetences
     */
    public function testEditActionWitCancel()
    {
        $this->setUpAction();

        $qualificationDetails = [
            'qualification-details' => [
                'id' => 1,
                'version' => 1,
                'issuedDate' => '2014-01-01',
                'serialNo' => '123',
                'qualificationType' => 'AR',
                'countryCode' => 'GB'
            ]
        ];

        $postEditForm = array_merge($qualificationDetails, ['form-actions' => ['cancel' => []]]);

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with($qualificationDetails)
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($qualificationDetails)
            ->shouldReceive('setData')
            ->with(array_merge($qualificationDetails, ['form-actions' => ['cancel' => []]]))
            ->getMock();

        $mockTmQualification = m::mock()
            ->shouldReceive('getQualification')
            ->with(1)
            ->andReturn(
                [
                    'id' => 1,
                    'version' => 1,
                    'issuedDate' => '2014-01-01',
                    'serialNo' => '123',
                    'qualificationType' => ['id' => 'AR'],
                    'countryCode' => ['id' => 'GB']
                ]
            )
            ->getMock();

        $this->sm->setService('Entity\TmQualification', $mockTmQualification);

        $this->sut
            ->shouldReceive('getForm')
            ->with('qualification')
            ->andReturn($mockForm)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($postEditForm)
                ->getMock()
            )
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->andReturn(true)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRouteAjax')
                ->with(null, ['transportManager' => 1])
                ->andReturn('redirect')
                ->getMock()
            )
            ->shouldReceive('getPersist')
            ->andReturn(true)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('content')
                ->getMock()
            );

        $response = $this->sut->editAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test delete TM file
     * 
     * @group tmCompetences1
     */
    public function testDeleteTmFile()
    {
        $this->setUpAction();

        $mockEntityDocumentService = m::mock()
            ->shouldReceive('getIdentifier')
            ->with(1)
            ->andReturn('identifier')
            ->shouldReceive('delete')
            ->with(1)
            ->andReturn(true)
            ->getMock();

        $mockUploaderService = m::mock()
            ->shouldReceive('getUploader')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('identifier')
                ->getMock()
            )
            ->getMock();

        $this->sm->setService('FileUploader', $mockUploaderService);
        $this->sm->setService('Entity\Document', $mockEntityDocumentService);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getFromRoute')
            ->with('action')
            ->andReturn('edit')
            ->shouldReceive('getFromRoute')
            ->with('title')
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock()
                ->shouldReceive('toRouteAjax')
                ->with(null, ['transportManager' => 1, 'action' => 'edit'], [], true)
                ->andReturn('redirect')
                ->getMock()
            );

        $this->assertEquals('redirect', $this->sut->deleteTmFile(1));
    }

    /**
     * Test get documents
     * 
     * @group tmCompetences
     */
    public function testGetDocuments()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1);

        $mockTransportManager = m::mock()
            ->shouldReceive('getDocuments')
            ->with(
                1,
                CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
            )
            ->andReturn(['documents'])
            ->getMock();

        $this->sm->setService('Entity\TransportManager', $mockTransportManager);

        $this->assertEquals(['documents'], $this->sut->getDocuments());
    }

    /**
     * Test process certificate file upload
     * 
     * @group tmCompetences
     */
    public function testProcessCertificateFileUpload()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1);

        $mockFileUploader = m::mock()
            ->shouldReceive('getUploader')
            ->andReturn(
                m::mock()
                ->shouldReceive('setFile')
                ->with(['name' => 'name.txt'])
                ->shouldReceive('upload')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getName')
                    ->andReturn('name')
                    ->shouldReceive('getIdentifier')
                    ->andReturn('identifier')
                    ->shouldReceive('getSize')
                    ->andReturn(10)
                    ->shouldReceive('getExtension')
                    ->andReturn('extension')
                    ->getMock()
                )
                ->getMock()
            )
            ->getMock();

        $this->sm->setService('FileUploader', $mockFileUploader);

        $mockDocumentService = m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'filename'         => 'name',
                    'identifier'       => 'identifier',
                    'size'             => 10,
                    'fileExtension'    => 'doc_extension',
                    'transportManager' => 1,
                    'description'      => 'name.txt',
                    'category'         => CategoryDataService::CATEGORY_TRANSPORT_MANAGER,
                    'subCategory'      => CategoryDataService::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $this->sm->setService('Entity\Document', $mockDocumentService);

        $this->assertEquals(['id' => 1], $this->sut->processCertificateFileUpload(['name' => 'name.txt'], []));
    }
}
