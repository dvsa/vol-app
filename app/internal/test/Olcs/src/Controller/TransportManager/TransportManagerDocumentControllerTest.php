<?php

/**
 * Transport manager document controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace OlcsTest\Controller\TransportManager;

use Mockery as m;
use OlcsTest\Bootstrap;

/**
 * Transport manager document controller tests
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class TransportManagerDocumentControllerTest extends \Mockery\Adapter\Phpunit\MockeryTestCase
{
    /**
     * @var m\MockInterface|\Zend\Mvc\Controller\PluginManager
     */
    protected $pluginManager;

    /**
     * @var Zend\ServiceManager\ServiceManager
     */
    protected $sm;

    protected $sut;

    public function setUp()
    {
        parent::setUp();
        $this->sut = m::mock('Olcs\Controller\TransportManager\TransportManagerDocumentController')
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * Test the documents action
     */
    public function testDocumentsAction()
    {
        $tmId = 69;

        // mock tmId route param
        $this->sut->shouldReceive('getFromRoute')->with('transportManager')->andReturn($tmId);
        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);

        // mock tm details rest call
        $this->sm->setService(
            'Entity\TransportManager',
            m::mock('\StdClass')
                ->shouldReceive('getTmDetails')
                ->with($tmId)
                ->andReturn($this->getTmStub($tmId))
                ->getMock()
        );

        // mock document REST calls
        $this->sm->setService(
            'Helper\Rest',
            $this->getMockRestHelperForDocuments()
        );

        // mock table service
        $this->sm->setService(
            'Table',
            m::mock('\Common\Service\Table\TableBuilder')
                ->shouldReceive('buildTable')
                ->andReturnSelf()
                ->shouldReceive('render')
                ->getMock()
        );

        // mock script helper
        $this->sm->setService(
            'Script',
            m::mock('\Common\Service\Script\ScriptFactory')
                ->shouldReceive('loadFiles')
                ->with(['documents', 'table-actions'])
                ->getMock()
        );

        // mock form
        $this->sut->shouldReceive('getForm')->with('documents-home')->andReturn(
            m::mock()
                ->shouldReceive('get')
                ->andReturn(
                    m::mock()->shouldReceive('setValueOptions')->getMock()
                )
                ->shouldReceive('remove')
                ->shouldReceive('setData')
                ->getMock()
            );

        $this->sut->shouldReceive('getSearchForm');

        $view = $this->sut->documentsAction();

        $this->assertInstanceOf('\Zend\View\Model\ViewModel', $view);
    }

    /**
     * Test the documents action throws an exception when transport manager
     * with given id doesn't exist
     */
    public function testDocumentsActionWhenTmNotFound()
    {
        $tmId = 99;
        $this->setExpectedException(
            'Common\Exception\ResourceNotFoundException',
            'Transport Manager with id [99] does not exist'
        );

        // mock tmId route param
        $this->sut->shouldReceive('getFromRoute')->with('transportManager')->andReturn($tmId);
        $this->sut->shouldReceive('params->fromRoute')->with('transportManager')->andReturn($tmId);

        // mock tm details rest call
        $this->sm->setService(
            'Entity\TransportManager',
            m::mock('\StdClass')
                ->shouldReceive('getTmDetails')
                ->with($tmId)
                ->andReturn(false) // TM not found
                ->getMock()
        );

        $this->sut->documentsAction();
    }

    /**
     * Test that documents action redirects to upload with a valid POST
     */
    public function testDocumentsActionWithUploadRedirectsToUpload()
    {
        $tmId = 69;

        $this->sut->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                    ->shouldReceive('isPost')
                    ->andReturn(true)
                    ->getMock()
            );

        $this->sut->shouldReceive('params')
            ->andReturn(
                m::mock()
                    ->shouldReceive('fromPost')
                    ->with('action')
                    ->andReturn('upload')
                    ->getMock()
            );

        $this->sut->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn($tmId);

        $this->sut->shouldReceive('redirect')
            ->andReturn(
                m::mock('\StdClass')
                    ->shouldReceive('toRoute')
                    ->with(
                        'transport-manager/documents/upload',
                        ['transportManager' => 69]
                    )
                    ->andReturn('thisistheredirect')
                    ->getMock()
            );

        $response = $this->sut->documentsAction();

        $this->assertEquals('thisistheredirect', $response);
    }

    protected function getMockRestHelperForDocuments()
    {
        return m::mock('Common\Service\Helper\RestHelperService')
            ->shouldReceive('makeRestCall')
            ->with(
                'DocumentSearchView',
                'GET',
                [
                    'sort'  => "issuedDate",
                    'order' => "DESC",
                    'page'  => 1,
                    'limit' => 10,
                    'tmId'  => 69
                ],
                m::any() // last param is usually a blank bundle specifier
            )
            ->andReturn([])
            ->shouldReceive('makeRestCall')
            ->with(
                'Category',
                'GET',
                [
                    'limit' => 100,
                    'sort' => 'description',
                    'isDocCategory' => true,
                ],
                m::any()
            )
            ->shouldReceive('makeRestCall')
            ->with(
                'SubCategory',
                'GET',
                [
                    'sort'      => "subCategoryName",
                    'order'     => "ASC",
                    'page'      => 1,
                    'limit'     => 100,
                    'tmId'      => 69,
                    'isDoc'     => true
                ],
                m::any()
            )
            ->shouldReceive('makeRestCall')
            ->with(
                'RefData',
                'GET',
                [
                    'refDataCategoryId' => 'document_type',
                    'limit'=>100,
                    'sort'=>'description'
                ],
                m::any()
            )
            ->getMock();
    }

    protected function getTmStub($id)
    {
        return array(
            'version' => 1,
            'contactDetails' => [
                'id' => $id,
                'version' => 1,
                'emailAddress' => 'email@address.com',
                'person' => [
                    'id' => 1,
                    'version' => 1,
                    'forename' => 'First',
                    'familyName' => 'Last',
                    'title' => 'Mr',
                    'birthDate' => '1973-01-01',
                    'birthPlace' => 'London'
                ],
                'address' => [
                    'id' => 1,
                    'version' => 1,
                    'addressLine1' => 'addressLine1',
                    'addressLine2' => 'addressLine2',
                    'addressLine3' => 'addressLine3',
                    'addressLine4' => 'addressLine4',
                    'town' => 'Town',
                    'postcode' => 'PC'
                ],
                'contactType' => [
                    'id' => 'ct_tm'
                ]
            ],
            'tmType' => [
                'id' => 'tm_t_B'
            ],
            'tmStatus' => [
                'id' => 'tm_st_A'
            ]
        );
    }
}
