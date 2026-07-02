<?php

namespace CommonTest\Common\Controller\Lva\Traits;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use CommonTest\Bootstrap;
use Laminas\Http\Response;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CRUD Table Trait Test
 *
 * @covers \Common\Controller\Lva\Traits\CrudTableTrait
 */
class CrudTableTraitTest extends MockeryTestCase
{
    public $mockFlashMessengerHelper;
    public $mockFormHelper;
    /** @var  Stubs\CrudTableTraitStub|m\MockInterface */
    protected $sut;

    /** @var  \Laminas\ServiceManager\ServiceManager */
    protected $sm;

    protected Response|m\MockInterface $response;

    #[\Override]
    protected function setUp(): void
    {
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);

        $this->response = m::mock(Response::class);

        $this->sut = m::mock(Stubs\CrudTableTraitStub::class, [$this->mockFlashMessengerHelper, $this->mockFormHelper])
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
    }

    public function testHandlePostSaveWithAddAnother(): void
    {
        $prefix = 'unit_Prdx';
        $options = ['unit_options'];

        $redirectMock = m::mock()
            ->shouldReceive('toRoute')
            ->with(
                null,
                [
                    'application' => 123,
                    'action' => 'unit_Prdx-add'
                ],
                $options
            )
            ->andReturn('redirect')
            ->getMock();

        $this->sut->shouldReceive('getIdentifierIndex')
            ->andReturn('application')
            ->shouldReceive('getIdentifier')
            ->andReturn(123)
            ->shouldReceive('isButtonPressed')
            ->with('addAnother')
            ->andReturn(true)
            ->shouldReceive('redirect')
            ->andReturn($redirectMock)
            ->shouldReceive('params')
            ->with('action')
            ->andReturn('add');

        $this->mockFlashMessengerHelper
            ->shouldReceive('addSuccessMessage')
            ->with('section.add.fake-section');

        $this->assertEquals(
            'redirect',
            $this->sut->callHandlePostSave($prefix, $options)
        );
    }

    public function testHandlePostSave(): void
    {
        $prefix = 'unit_Prdx';
        $options = ['unit_options'];

        $route = 'unit_Route';

        $redirectMock = m::mock()
            ->shouldReceive('toRouteAjax')
            ->with($route, ['application' => 123], $options)
            ->andReturn('redirect')
            ->getMock();

        $this->sut->shouldReceive('getBaseRoute')->once()->andReturn($route);

        $this->sut->shouldReceive('getIdentifierIndex')
            ->andReturn('application')
            ->shouldReceive('getIdentifier')
            ->andReturn(123)
            ->shouldReceive('isButtonPressed')
            ->with('addAnother')
            ->andReturn(false)
            ->shouldReceive('redirect')
            ->andReturn($redirectMock)
            ->shouldReceive('params')
            ->with('action')
            ->andReturn('add');

        $this->mockFlashMessengerHelper
            ->shouldReceive('addSuccessMessage')
            ->with('section.add.fake-section');

        $this->assertEquals(
            'redirect',
            $this->sut->callHandlePostSave($prefix, $options)
        );
    }

    public function testDeleteAction(): void
    {
        $request = m::mock()
            ->shouldReceive('isPost')
            ->andReturn(false)
            ->getMock();

        $form = m::mock();

        $this->sut
            ->shouldReceive('getRequest')->once()->andReturn($request)
            ->shouldReceive('render')
            ->with('delete', $form, ['sectionText' => 'delete.confirmation.text'])
            ->andReturn($this->response);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('GenericDeleteConfirmation', $request)
            ->andReturn($form);

        $this->assertSame(
            $this->response,
            $this->sut->deleteAction()
        );
    }

    public function testDeleteActionWithPost(): void
    {
        $route = 'unit_Route';
        $queryParams = ['unit_queryParams'];

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage');

        $redirectMock = m::mock()
            ->shouldReceive('toRouteAjax')
            ->with(
                $route,
                [
                    'application' => 123
                ],
                [
                    'query' => $queryParams,
                ]
            )
            ->andReturn($this->response)
            ->getMock();

        $mockRequest = m::mock(\Laminas\Http\Request::class);
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $mockRequest->shouldReceive('getQuery->toArray')->andReturn($queryParams);

        $this->sut
            ->shouldReceive('getBaseRoute')->once()->andReturn($route)
            ->shouldReceive('getRequest')->once()->andReturn($mockRequest)
            ->shouldReceive('getIdentifierIndex')
            ->andReturn('application')
            ->shouldReceive('getIdentifier')
            ->andReturn(123)
            ->shouldReceive('params')
            ->with('action')
            ->andReturn('delete')
            ->shouldReceive('delete')
            ->shouldReceive('redirect')
            ->andReturn($redirectMock);

        $this->assertSame(
            $this->response,
            $this->sut->deleteAction()
        );
    }
}
