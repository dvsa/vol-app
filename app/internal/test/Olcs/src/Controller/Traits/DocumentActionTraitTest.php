<?php

/**
 * Document action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\Traits;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Zend\View\Model\ViewModel;

/**
 * Document action trait tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 *
 */
class DocumentActionTraitTest extends AbstractHttpControllerTestCase
{
    protected $post = [];

    protected $mockRedirect;

    public function setUpAction()
    {
        $this->markTestSkipped();
        $this->sut = m::mock('\Olcs\Controller\TransportManager\TransportManagerDocumentController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test documents actions with no post
     *
     * @group documentActionTrait
     * @return array
     */
    public function testDocumentsActionNoPost()
    {
        $this->setUpAction();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('layout/docs-attachments-list')
            ->getMock();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('getDocumentView')
            ->andReturn($mockView)
            ->shouldReceive('loadScripts')
            ->with(['documents', 'table-actions'])
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->documentsAction());
    }

    /**
     * Test documents action with post and new letter action
     *
     * @group documentActionTrait
     * @return array
     */
    public function testDocumentsActionPostNewLetterAction()
    {
        $this->setUpAction();
        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromPost')
                ->with('action')
                ->once()
                ->andReturn('new letter')
                ->once()
                ->getMock()
            )
            ->shouldReceive('getDocumentRouteParams')
            ->andReturn(['some' => 'params'])
            ->shouldReceive('getDocumentRoute')
            ->andReturn('route')
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock('Zend\Http\Redirect')
                ->shouldReceive('toRoute')
                ->with('route/generate', ['some' => 'params'])
                ->andReturnSelf()
                ->getMock()
            );
        $this->assertInstanceOf('Zend\Http\Redirect', $this->sut->documentsAction());
    }

    /**
     * Test documents action with post and delete action
     *
     * @group documentActionTrait
     * @return array
     */
    public function testDocumentsActionPostDeleteAction()
    {
        $this->setUpAction();
        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromPost')
                ->with('action')
                ->once()
                ->andReturn('delete')
                ->shouldReceive('fromPost')
                ->with('id', [])
                ->andReturn([1,2])
                ->once()
                ->getMock()
            )
            ->shouldReceive('getDocumentRouteParams')
            ->andReturn(['some' => 'params'])
            ->shouldReceive('getDocumentRoute')
            ->andReturn('route')
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock('Zend\Http\Redirect')
                ->shouldReceive('toRoute')
                ->with('route/delete', ['some' => 'params', 'tmpId' => '1,2'])
                ->andReturnSelf()
                ->getMock()
            );
        $this->assertInstanceOf('Zend\Http\Redirect', $this->sut->documentsAction());
    }

    /**
     * Test delete action with no post
     *
     * @group documentActionTrait
     * @return array
     */
    public function testDeleteActionNoPost()
    {
        $this->setUpAction();

        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('internal.documents.delete.delete_message')
            ->andReturn('message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('tmpId')
                ->andReturn('1,2')
                ->getMock()
            )
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn(new ViewModel());

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->sut->deleteDocumentAction());
    }

    /**
     * Test delete action with post
     *
     * @group documentActionTrait
     * @return array
     */
    public function testDeleteActionPost()
    {
        $this->setUpAction();

        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('internal.documents.delete.delete_message')
            ->andReturn('message')
            ->getMock();
        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('tmpId')
                ->andReturn('1,2')
                ->getMock()
            )
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn('response')
            ->shouldReceive('deleteFile')
            ->with(1)
            ->once()
            ->shouldReceive('deleteFile')
            ->with(2)
            ->once()
            ->shouldReceive('addErrorMessage')
            ->with('internal.documents.delete.deleted_successfully')
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock('Zend\Http\Redirect')
                ->shouldReceive('toRouteAjax')
                ->with('route', ['some' => 'params'])
                ->andReturnSelf()
                ->getMock()
            )
            ->shouldReceive('getDocumentRoute')
            ->andReturn('route')
            ->shouldReceive('getDocumentRouteParams')
            ->andReturn(['some' => 'params']);

        $this->assertInstanceOf('Zend\Http\Redirect', $this->sut->deleteDocumentAction());
    }

    /**
     * Test documents action with post and split screen
     */
    public function testDocumentsActionPostSplitScreenAction()
    {
        $this->setUpAction();

        $documentData = [
            'identifier' => '54321asdf',
            'filename' => 'Foo.pdf'
        ];

        $expectedDocParams = [
            'file' => '54321asdf',
            'name' => 'Foo.pdf'
        ];

        $expectedQuery = [
            'query' => [
                'inline' => 1
            ]
        ];

        $expectedFragment = base64_encode('URL1|URL2');

        // Mocks
        $mockDocument = m::mock();
        $mockRequest = m::mock();
        $mockParams = m::mock();
        $mockUrl = m::mock();
        $this->sm->setService('Entity\Document', $mockDocument);

        // Expectations
        $mockDocument->shouldReceive('getById')
            ->once()
            ->andReturn($documentData);

        $mockRequest->shouldReceive('isPost')
            ->andReturn(true);

        $mockParams->shouldReceive('fromPost')
            ->once()
            ->with('action')
            ->andReturn('split')
            ->shouldReceive('fromPost')
            ->once()
            ->with('id', [])
            ->andReturn(111);

        $mockUrl->shouldReceive('fromRoute')
            ->once()
            ->with(null, [], [], true)
            ->andReturn('URL1')
            ->shouldReceive('fromRoute')
            ->once()
            ->with('getfile', $expectedDocParams, $expectedQuery)
            ->andReturn('URL2');

        $this->sut->shouldReceive('getRequest')
            ->andReturn($mockRequest)
            ->shouldReceive('params')
            ->andReturn($mockParams)
            ->shouldReceive('url')
            ->andReturn($mockUrl)
            ->shouldReceive('getDocumentRouteParams')
            ->andReturn(['some' => 'params'])
            ->shouldReceive('getDocumentRoute')
            ->andReturn('route');

        $this->sut->shouldReceive('redirect->toRouteAjax')
            ->with('split-screen', [], ['fragment' => $expectedFragment])
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->documentsAction());
    }
}
