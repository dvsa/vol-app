<?php
/**
 * Class User Forgot Username Controller Test
 */
namespace OlcsTest\Controller;

use Dvsa\Olcs\Transfer\Command\User\RemindUsernameSelfserve as RemindUsernameDto;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\UserForgotUsernameController as Sut;
use OlcsTest\Bootstrap;

/**
 * Class User Forgot Username Controller Test
 */
class UserForgotUsernameControllerTest extends TestCase
{
    protected $sut;
    protected $sm;

    public function setUp()
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();

        $this->sut->setServiceLocator($this->sm);
    }

    public function testIndexActionForGet()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(false);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock('Common\Form\Form');

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);
        $this->assertEquals('olcs/user-forgot-username/index', $view->getTemplate());
    }

    public function testIndexActionForPostWithCancel()
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock('Common\Form\Form');

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('index')
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostSingle()
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

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

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

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);
        $this->assertEquals('olcs/user-forgot-username/check-email', $view->getTemplate());
    }

    public function testIndexActionForPostMultiple()
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

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

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

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);
        $this->assertEquals('olcs/user-forgot-username/ask-admin', $view->getTemplate());
    }

    public function testIndexActionForPostNotFound()
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

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);
        $mockForm->shouldReceive('get')->with('fields')->once()->andReturnSelf();
        $mockForm->shouldReceive('get')->with('emailAddress')->once()->andReturnSelf();
        $mockForm->shouldReceive('setMessages')->with(['ERR_FORGOT_USERNAME_NOT_FOUND'])->once()->andReturnSelf();

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

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

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);
        $this->assertEquals('olcs/user-forgot-username/index', $view->getTemplate());
    }

    public function testIndexActionForPostWithError()
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

        $mockForm = m::mock('Common\Form\Form');
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $mockFormHelper = m::mock();
        $mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserForgotUsername', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->sm->setService('Helper\Form', $mockFormHelper);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $this->sut->shouldReceive('handleCommand')->with(m::type(RemindUsernameDto::class))->andReturn($response);

        $mockFlashMessengerHelper = m::mock();
        $mockFlashMessengerHelper
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('unknown-error');
        $this->sm->setService('Helper\FlashMessenger', $mockFlashMessengerHelper);

        $view = $this->sut->indexAction();

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $view);
        $this->assertEquals('olcs/user-forgot-username/index', $view->getTemplate());
    }
}
