<?php

namespace OlcsTest\Controller;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\Http\Request;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\Plugin\Params;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;
use Laminas\Navigation\Navigation;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\ConversationsController as Sut;
use Olcs\Form\Model\Form\Message\Reply;
use ReflectionClass;
use LmcRbacMvc\Service\AuthorizationService;

class ConversationsControllerTest extends TestCase
{
    protected $sut;

    public function setUp(): void
    {
        $this->mockNiTextTranslationUtil = m::mock(NiTextTranslation::class)->makePartial();
        $this->mockAuthService = m::mock(AuthorizationService::class)->makePartial();
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class)->makePartial();
        $this->mockTableFactory = m::mock(TableFactory::class)->makePartial();
        $this->mockFormHelperService = m::mock(FormHelperService::class)->makePartial();
        $this->mockNavigation = m::mock(Navigation::class)->shouldIgnoreMissing();
        $this->mockForm = m::mock(Form::class);
        $this->mockParams = m::mock(Params::class);

        $this->sut = m::mock(Sut::class)
                      ->makePartial()
                      ->shouldAllowMockingProtectedMethods();

        $reflectionClass = new ReflectionClass(Sut::class);
        $this->setMockedProperties($reflectionClass, 'niTextTranslationUtil', $this->mockNiTextTranslationUtil);
        $this->setMockedProperties($reflectionClass, 'authService', $this->mockAuthService);
        $this->setMockedProperties($reflectionClass, 'flashMessengerHelper', $this->mockFlashMessengerHelper);
        $this->setMockedProperties($reflectionClass, 'tableFactory', $this->mockTableFactory);
        $this->setMockedProperties($reflectionClass, 'formHelperService', $this->mockFormHelperService);
        $this->setMockedProperties($reflectionClass, 'navigationService', $this->mockNavigation);

        $this->mockFormHelperService->shouldReceive('createForm')
                                    ->once()
                                    ->with(Reply::class, true, false)
                                    ->andReturn($this->mockForm);

        $this->mockFormHelperService->shouldReceive('setFormActionFromRequest')
                                    ->once()
                                    ->withArgs(
                                        function ($form, $request) {
                                            $this->assertInstanceOf(Form::class, $form);
                                            return true;
                                        },
                                    );
    }

    public function setMockedProperties(ReflectionClass $reflectionClass, string $property, $value): void
    {
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($this->sut, $value);
    }

    public function testViewAction(): void
    {
        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
                     ->andReturn(true);
        $mockResponse->shouldReceive('getResult')
                     ->andReturn([
                         'extra' => [
                             'conversation' => [
                                 'isClosed' => true,
                             ],
                         ],
                     ]);

        $mockHandleQuery = m::mock(HandleQuery::class)
                            ->makePartial();
        $mockHandleQuery->shouldReceive('__invoke')
                        ->andReturn($mockResponse);

        $this->mockParams->shouldReceive('fromQuery')
                         ->with('page', 1)
                         ->andReturn(1);
        $this->mockParams->shouldReceive('fromQuery')
                         ->with('limit', 10)
                         ->andReturn(10);
        $this->mockParams->shouldReceive('fromQuery')
                         ->withNoArgs()
                         ->andReturn([]);
        $this->mockParams->shouldReceive('fromRoute')
                         ->with('conversationId')
                         ->andReturn(1);

        $this->sut->shouldReceive('params')
                  ->andReturn($this->mockParams);
        $this->sut->shouldReceive('plugin')
                  ->with('handleQuery')
                  ->andReturn($mockHandleQuery);

        $table = '<table/>';

        $this->mockTableFactory->shouldReceive('buildTable')
                               ->with(
                                   'messages-view',
                                   [],
                                   ['page' => 1, 'limit' => 10, 'conversation' => 1, 'query' => []],
                               )
                               ->andReturn($table);

        $this->mockNavigation
            ->shouldReceive('findBy->setActive')
            ->once();

        $view = $this->sut->viewAction();
        $this->assertInstanceOf(ViewModel::class, $view);
        $this->assertEquals($table, $view->getVariable('table'));
    }

    public function testReply(): void
    {
        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('isPost')
                    ->once()
                    ->andReturn(true);

        $this->mockParams->shouldReceive('fromPost')
                         ->once()
                         ->with('action')
                         ->andReturn('reply');
        $this->mockParams->shouldReceive('fromPost')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(['a' => 'b']);
        $this->mockParams->shouldReceive('fromRoute')
                         ->once()
                         ->with('conversation')
                         ->andReturn('1');
        $this->mockParams->shouldReceive('fromRoute')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(['a' => 'b']);

        $this->sut->shouldReceive('getRequest')
                  ->twice()
                  ->andReturn($mockRequest);

        $mockCommandReturn = m::mock(Response::class);
        $mockCommandReturn->shouldReceive('isOk')
                          ->once()
                          ->andReturn(true);

        $mockCommandHandler = m::mock(HandleCommand::class);
        $mockCommandHandler->shouldReceive('__invoke')
                           ->once()
                           ->withArgs(
                               function ($command) {
                                   $this->assertInstanceOf(CreateMessageCommand::class, $command);

                                   return true;
                               },
                           )
                           ->andReturn($mockCommandReturn);

        $this->sut->shouldReceive('plugin')
                  ->with('handleCommand')
                  ->andReturn($mockCommandHandler);

        $this->mockForm->shouldReceive('setData')
                       ->once()
                       ->with(['a' => 'b']);

        $mockFormElement = m::mock(Text::class);
        $mockFormElement->shouldReceive('setValue')
                        ->once()
                        ->with('1');
        $mockFormElement->shouldReceive('getValue')
                        ->once()
                        ->andReturn('abc');

        $this->mockForm->shouldReceive('get')
                       ->once()
                       ->with('id')
                       ->andReturn($mockFormElement);

        $mockFormActionsElement = m::mock(Fieldset::class);
        $mockFormActionsElement->shouldReceive('get')
                               ->once()
                               ->with('reply')
                               ->andReturn($mockFormElement);
        $this->mockForm->shouldReceive('get')
                       ->once()
                       ->with('form-actions')
                       ->andReturn($mockFormActionsElement);

        $this->mockForm->shouldReceive('isValid')
                       ->once()
                       ->with()
                       ->andReturn(true);

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')
                                       ->once()
                                       ->with('Reply submitted successfully');

        $mockViewModel = m::mock(ViewModel::class);
        $mockViewModel->shouldReceive('getVariable')
                      ->once()
                      ->with('table')
                      ->andReturn('<table/>');

        $mockRedirect = m::mock(Redirect::class);
        $mockRedirect->shouldReceive('toRoute')
                     ->once()
                     ->with('conversations/view', ['a' => 'b'])
                     ->andReturn($mockViewModel);

        $this->sut->shouldReceive('plugin')
                  ->once()
                  ->with('redirect')
                  ->andReturn($mockRedirect);

        $this->testViewAction();
    }
}
