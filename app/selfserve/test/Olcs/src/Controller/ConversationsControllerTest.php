<?php

namespace OlcsTest\Controller;

use Common\Controller\Plugin\HandleCommand;
use Common\Controller\Plugin\HandleQuery;
use Common\Controller\Plugin\Redirect;
use Common\Form\Form;
use Common\Rbac\User;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FileUploadHelperService;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Table\TableFactory;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Element\Hidden;
use Laminas\Form\Element\Text;
use Laminas\Form\Fieldset;
use Laminas\Http\Request;
use Laminas\Http\Response as HttpResponse;
use Laminas\Mvc\Controller\Plugin\Params;
use Dvsa\Olcs\Transfer\Command\Messaging\Message\Create as CreateMessageCommand;
use Laminas\Mvc\Controller\Plugin\Url;
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
        $this->mockUploadHelper = m::mock(FileUploadHelperService::class);

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
        $this->setMockedProperties($reflectionClass, 'uploadHelper', $this->mockUploadHelper);

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
                     ->andReturn(
                         [
                             'extra'       => [
                                 'conversation' => [
                                     'subject'  => 'Banana',
                                     'isClosed' => true,
                                 ],
                                 'application' => [
                                     'id' => 100000,
                                 ],
                                 'licence'     => [
                                     'licNo' => 'OK1234',
                                 ],
                             ],
                         ],
                     );

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

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('getUserData')
                 ->once()
                 ->andReturn(
                     [
                         'organisationUsers' => [
                             [
                                 'organisation' => [
                                     'isMessagingFileUploadEnabled' => true,
                                 ],
                             ],
                         ],
                     ],
                 );

        $mockUrl = m::mock(Url::class);
        $mockUrl->shouldReceive('fromRoute')
                ->once()
                ->with('conversations');

        $this->sut->shouldReceive('params')
                  ->andReturn($this->mockParams);
        $this->sut->shouldReceive('plugin')
                  ->with('handleQuery')
                  ->once()
                  ->andReturn($mockHandleQuery);
        $this->sut->shouldReceive('plugin')
                  ->with('currentUser')
                  ->andReturn($mockUser);
        $this->sut->shouldReceive('plugin')
                  ->with('url')
                  ->once()
                  ->andReturn($mockUrl);

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

        $mockFormElement = m::mock(Hidden::class);
        $mockFormElement->shouldReceive('setValue')
                        ->once();

        $this->mockForm->shouldReceive('get')
                       ->once()
                       ->with('correlationId')
                       ->andReturn($mockFormElement);

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
        $mockRequest->shouldReceive('getPost')
                    ->once()
                    ->withNoArgs()
                    ->andReturn([]);
        $mockRequest->shouldReceive('getPost')
                    ->with('correlationId')
                    ->once()
                    ->andReturn('123');

        $this->mockForm->shouldReceive('setData')
                       ->once()
                       ->with([]);

        $this->mockParams->shouldReceive('fromPost')
                         ->once()
                         ->with('action')
                         ->andReturn('reply');
        $this->mockParams->shouldReceive('fromRoute')
                         ->once()
                         ->with('conversation')
                         ->andReturn('1');
        $this->mockParams->shouldReceive('fromRoute')
                         ->once()
                         ->withNoArgs()
                         ->andReturn(['a' => 'b']);

        $this->sut->shouldReceive('getRequest')
                  ->times(5)
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

        $this->mockUploadHelper
            ->shouldReceive('setForm')
            ->once()
            ->andReturn($this->mockUploadHelper);

        $this->mockUploadHelper
            ->shouldReceive('setSelector')
            ->once()
            ->andReturn($this->mockUploadHelper);

        $this->mockUploadHelper
            ->shouldReceive('setUploadCallback')
            ->once()
            ->andReturn($this->mockUploadHelper);

        $this->mockUploadHelper
            ->shouldReceive('setDeleteCallback')
            ->once()
            ->andReturn($this->mockUploadHelper);

        $this->mockUploadHelper
            ->shouldReceive('setLoadCallback')
            ->once()
            ->andReturn($this->mockUploadHelper);

        $this->mockUploadHelper
            ->shouldReceive('setRequest')
            ->once()
            ->andReturn($this->mockUploadHelper);

        $this->mockUploadHelper
            ->shouldReceive('setCountSelector')
            ->once()
            ->andReturn($this->mockUploadHelper);

        $this->mockUploadHelper
            ->shouldReceive('process')
            ->once()
            ->andReturn(false);

        $this->sut->shouldReceive('plugin')
                  ->once()
                  ->with('redirect')
                  ->andReturn($mockRedirect);

        $this->testViewAction();
    }
}
