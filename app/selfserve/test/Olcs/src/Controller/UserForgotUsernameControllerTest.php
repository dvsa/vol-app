<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Transfer\Command\User\RemindUsernameSelfserve as RemindUsernameDto;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\UserForgotUsernameController as Sut;
use ReflectionClass;
use LmcRbacMvc\Service\AuthorizationService;

class UserForgotUsernameControllerTest extends TestCase
{
    protected $sut;
    protected $sm;

    private $mockniTextTranslationUtil;

    private $mockauthService;

    private $mockflashMessengerHelper;

    private $mockformHelper;

    public function setUp(): void
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockniTextTranslationUtil = m::mock(NiTextTranslation::class)->makePartial();
        $this->mockauthService = m::mock(AuthorizationService::class)->makePartial();
        $this->mockformHelper = m::mock(FormHelperService::class)->makePartial();
        $this->mockflashMessengerHelper = m::mock(FlashMessengerHelperService::class)->makePartial();

        $reflectionClass = new ReflectionClass(Sut::class);
        $this->setMockedProperties($reflectionClass, 'niTextTranslationUtil', $this->mockniTextTranslationUtil);
        $this->setMockedProperties($reflectionClass, 'authService', $this->mockauthService);
        $this->setMockedProperties($reflectionClass, 'flashMessengerHelper', $this->mockflashMessengerHelper);
        $this->setMockedProperties($reflectionClass, 'formHelper', $this->mockformHelper);
    }

    /**
     * @psalm-param ReflectionClass<Sut> $reflectionClass
     */
    public function setMockedProperties(ReflectionClass $reflectionClass, string $property, m\LegacyMockInterface $value): void
    {
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setValue($this->sut, $value);
    }

    public function testIndexActionForGet(): void
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(false);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('olcs/user-forgot-username/index', $view->getTemplate());
    }

    public function testIndexActionForPostWithCancel(): void
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('index')
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostSingle(): void
    {
        $postData = [
            'fields' => [
                'licenceNumber' => 'AB12345678',
                'emailAddress' => 'steve@example.com'
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $result = [
            'messages' => ['USERNAME_REMINDER_SENT_SINGLE']
        ];

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($result);
        $this->sut->shouldReceive('handleCommand')->with(m::type(RemindUsernameDto::class))->andReturn($response);

        $placeholder = m::mock();
        $placeholder->shouldReceive('setPlaceholder')
            ->with('pageTitle', 'user-forgot-username.page.check-email.title')
            ->once();
        $this->sut->shouldReceive('placeholder')->andReturn($placeholder);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('olcs/user-forgot-username/check-email', $view->getTemplate());
    }

    public function testIndexActionForPostMultiple(): void
    {
        $postData = [
            'fields' => [
                'licenceNumber' => 'AB12345678',
                'emailAddress' => 'steve@example.com'
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $result = [
            'messages' => ['USERNAME_REMINDER_SENT_MULTIPLE']
        ];

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($result);
        $this->sut->shouldReceive('handleCommand')->with(m::type(RemindUsernameDto::class))->andReturn($response);

        $placeholder = m::mock();
        $placeholder->shouldReceive('setPlaceholder')
            ->with('pageTitle', 'user-forgot-username.page.ask-admin.title')
            ->once();
        $this->sut->shouldReceive('placeholder')->andReturn($placeholder);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('olcs/user-forgot-username/ask-admin', $view->getTemplate());
    }

    public function testIndexActionForPostNotFound(): void
    {
        $postData = [
            'fields' => [
                'licenceNumber' => 'AB12345678',
                'emailAddress' => 'steve@example.com'
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);
        $mockForm->shouldReceive('get')->with('fields')->once()->andReturnSelf();
        $mockForm->shouldReceive('get')->with('emailAddress')->once()->andReturnSelf();
        $mockForm->shouldReceive('setMessages')->with(['ERR_FORGOT_USERNAME_NOT_FOUND'])->once()->andReturnSelf();

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $result = [
            'messages' => ['ERR_USERNAME_NOT_FOUND']
        ];

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($result);
        $this->sut->shouldReceive('handleCommand')->with(m::type(RemindUsernameDto::class))->andReturn($response);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('olcs/user-forgot-username/index', $view->getTemplate());
    }

    public function testIndexActionForPostWithError(): void
    {
        $postData = [
            'fields' => [
                'licenceNumber' => 'AB12345678',
                'emailAddress' => 'steve@example.com'
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $this->sut->shouldReceive('handleCommand')->with(m::type(RemindUsernameDto::class))->andReturn($response);

        $this->mockflashMessengerHelper
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('unknown-error');

        $view = $this->sut->indexAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('olcs/user-forgot-username/index', $view->getTemplate());
    }
}
