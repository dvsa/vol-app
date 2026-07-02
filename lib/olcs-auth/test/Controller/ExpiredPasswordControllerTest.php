<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Auth\Controller;

use Common\Service\Cqrs\Command\CommandSender;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Dvsa\Olcs\Auth\Container\AuthChallengeContainer;
use Dvsa\Olcs\Auth\Controller\ExpiredPasswordController;
use Dvsa\Olcs\Auth\Form\ChangePasswordForm;
use Dvsa\Olcs\Transfer\Result\Auth\ChangeExpiredPasswordResult;
use Laminas\Authentication\Storage\Session;
use Laminas\Form\ElementInterface;
use Laminas\Form\Form;
use Laminas\Http\Request;
use Laminas\InputFilter\InputFilterInterface;
use Laminas\Mvc\Controller\Plugin\Layout;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Laminas\Mvc\Controller\PluginManager;
use Laminas\Stdlib\Parameters;
use Laminas\View\Model\ViewModel;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use RuntimeException;

/**
 * @see ExpiredPasswordController
 */
class ExpiredPasswordControllerTest extends MockeryTestCase
{
    private ExpiredPasswordController $sut;

    private m\MockInterface|m\ExpectationInterface $formHelper;

    private m\MockInterface|m\ExpectationInterface $commandSender;

    private m\MockInterface|m\ExpectationInterface $flashMessenger;

    private m\MockInterface|m\ExpectationInterface $redirect;

    private m\MockInterface|m\ExpectationInterface $authChallengeContainer;

    private m\MockInterface|m\ExpectationInterface $sessionContainer;

    private $layout;

    private $pm;

    protected function setUp(): void
    {
        $this->formHelper = m::mock(FormHelperService::class);
        $this->commandSender = m::mock(CommandSender::class)->makePartial();
        $this->flashMessenger = m::mock(FlashMessengerHelperService::class);
        $this->redirect = m::mock(Redirect::class)->makePartial();
        $this->layout = m::mock(Layout::class)->makePartial();
        $this->authChallengeContainer = m::mock(AuthChallengeContainer::class);
        $this->sessionContainer = m::mock(Session::class);

        $this->pm = m::mock(PluginManager::class);
        $this->pm->shouldReceive('setController')->with(m::type(ExpiredPasswordController::class));

        $this->sut = new ExpiredPasswordController(
            $this->authChallengeContainer,
            $this->commandSender,
            $this->flashMessenger,
            $this->formHelper,
            $this->sessionContainer
        );
        $this->sut->setPluginManager($this->pm);

        parent::setUp();
    }

    public function testIndexActionForGet(): void
    {
        $this->pm->expects('get')->with('layout', null)->andReturn($this->layout);
        $this->layout->expects('__invoke')->with('auth/layout');

        $form = m::mock(Form::class);
        $form->expects('remove')
            ->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')
            ->with('oldPassword');

        $form->expects('getInputFilter')
            ->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }

    public function testIndexActionForInvalidPostRequest(): void
    {
        $this->pm->expects('get')->with('layout', null)->andReturn($this->layout);
        $this->layout->expects('__invoke')->with('auth/layout');

        $form = m::mock(Form::class);

        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')->with('oldPassword');

        $form->expects('getInputFilter')->andReturn($inputFilter);

        $this->formHelper->expects('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('GET');

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }

    public function testIndexActionForPostWithInvalidDataOnForm(): void
    {
        $post = [];

        $this->pm->expects('get')->with('layout', null)->andReturn($this->layout);
        $this->layout->expects('__invoke')->with('auth/layout');

        $form = m::mock(Form::class);
        $form->expects('setData');
        $form->expects('isValid')->andReturn(false);

        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')->with('oldPassword');

        $form->expects('getInputFilter')->andReturn($inputFilter);

        $this->formHelper->expects('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }

    public function testIndexActionForPostWithValidDataSuccess(): void
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'new-password'
        ];

        $this->pm->expects('get')->with('redirect', null)->andReturn($this->redirect);

        $form = m::mock(Form::class);
        $form->expects('setData');
        $form->expects('isValid')->andReturn(true);
        $form->expects('getData')->andReturn($post);
        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')->with('oldPassword');
        $form->expects('getInputFilter')->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');
        $this->authChallengeContainer
            ->expects('clear');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnTrue();
        $mockResponse->expects('getResult')
            ->andReturn(['flags' =>
                ['code' => 1,
                    'identity' =>
                        ['username' => 'example']]]);

        $this->commandSender->expects('send')
            ->andReturn($mockResponse);

        $this->sessionContainer
            ->expects('write')
            ->with(['username' => 'example']);

        $this->redirect
            ->expects('toRoute')
            ->with(ExpiredPasswordController::ROUTE_INDEX)
            ->andReturn('REDIRECT');

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataWrongChallenge(): void
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->expects('setData');
        $form->expects('isValid')->andReturn(true);
        $form->expects('getData')->andReturn($post);
        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')->with('oldPassword');
        $form->expects('getInputFilter')->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn('challenge-name');

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ExpiredPasswordController::MESSAGE_CHALLENGE_NOT_NEW_PASSWORD_REQUIRED);

        $this->sut->indexAction();
    }

    public function testIndexActionForPostWithValidDataResultNotOk(): void
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->expects('setData');
        $form->expects('isValid')->andReturn(true);
        $form->expects('getData')->andReturn($post);
        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')->with('oldPassword');
        $form->expects('getInputFilter')->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnFalse();

        $this->commandSender->expects('send')
            ->andReturn($mockResponse);

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage(ExpiredPasswordController::MESSAGE_RESULT_NOT_OK);

        $this->sut->indexAction();
    }

    /**
     * @dataProvider errorMessagesDataProvider
     */
    public function testIndexActionForPostWithValidDataNewPasswordInvalid($errorMessage): void
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $this->pm->expects('get')->with('layout', null)->andReturn($this->layout);
        $this->layout->expects('__invoke')->with('auth/layout');

        $form = m::mock(Form::class);
        $form->expects('setData');
        $form->expects('isValid')->andReturn(true);
        $form->expects('getData')->andReturn($post);
        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')
            ->with('oldPassword');
        $form->expects('getInputFilter')
            ->andReturn($inputFilter);

        $element = m::mock(ElementInterface::class);

        $element->expects('setMessages');
        $form->expects('get')
            ->with('newPassword')
            ->andReturn($element);

        $this->formHelper->expects('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnTrue();
        $mockResponse->expects('getResult')
            ->andReturn([
                'flags' => [
                    'code' =>  $errorMessage
                ]
            ]);
        $this->commandSender->expects('send')
            ->andReturn($mockResponse);

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $result = $this->sut->indexAction();

        $this->assertInstanceOf(ViewModel::class, $result);
        $this->assertEquals('auth/expired-password', $result->getTemplate());
    }


    /**
     * dataProvider for testIndexActionForPostWithValidDataNewPasswordInvalid
     */
    public function errorMessagesDataProvider(): array
    {
        return [
            'FAILURE_NEW_PASSWORD_INVALID' => [ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_INVALID],
            'FAILURE_NEW_PASSWORD_MATCHES_OLD' => [ChangeExpiredPasswordResult::FAILURE_NEW_PASSWORD_MATCHES_OLD],
        ];
    }

    public function testIndexActionForPostWithValidDataNotAuthorizedFailure(): void
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $this->pm->expects('get')->with('redirect', null)->andReturn($this->redirect);

        $form = m::mock(Form::class);
        $form->expects('setData');
        $form->expects('isValid')->andReturn(true);
        $form->expects('getData')->andReturn($post);
        $form->expects('remove')->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->expects('remove')->with('oldPassword');
        $form->expects('getInputFilter')->andReturn($inputFilter);

        $this->formHelper->expects('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->expects('getChallengeName')
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->expects('getChallengeSession')
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->expects('getChallengedIdentity')
            ->andReturn('identity');

        $this->flashMessenger
            ->expects('addErrorMessage')
            ->with('message1');
        $this->flashMessenger
            ->expects('addErrorMessage')
            ->with('message2');

        $mockResponse = m::mock(Response::class);
        $mockResponse->expects('isOk')
            ->andReturnTrue();
        $mockResponse->expects('getResult')
            ->andReturn([
                'flags' => [
                    'code' => ChangeExpiredPasswordResult::FAILURE_NOT_AUTHORIZED,
                    'messages' => [
                        'message1',
                        'message2'
                    ]
                ]
            ]);

        $this->commandSender->expects('send')
            ->andReturn($mockResponse);

        $this->redirect
            ->expects('toRoute')
            ->with(ExpiredPasswordController::ROUTE_LOGIN)
            ->andReturn('REDIRECT');

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->assertEquals('REDIRECT', $this->sut->indexAction());
    }

    public function testIndexActionForPostWithValidDataInvalidResponse(): void
    {
        $post = [
            'newPassword' => 'new-password',
            'confirmPassword' => 'confirm-password'
        ];

        $form = m::mock(Form::class);
        $form->shouldReceive('setData')->once();
        $form->shouldReceive('isValid')->once()->andReturn(true);
        $form->shouldReceive('getData')->once()->andReturn($post);
        $form->shouldReceive('remove')->once()->with('oldPassword');

        $inputFilter = m::mock(InputFilterInterface::class);
        $inputFilter->shouldReceive('remove')->once()->with('oldPassword');
        $form->shouldReceive('getInputFilter')->once()->andReturn($inputFilter);

        $this->formHelper->shouldReceive('createForm')
            ->with(ChangePasswordForm::class)
            ->andReturn($form);

        $this->authChallengeContainer
            ->shouldReceive('getChallengeName')
            ->once()
            ->andReturn(AuthChallengeContainer::CHALLENEGE_NEW_PASWORD_REQUIRED);
        $this->authChallengeContainer
            ->shouldReceive('getChallengeSession')
            ->once()
            ->andReturn('challenge-session');
        $this->authChallengeContainer
            ->shouldReceive('getChallengedIdentity')
            ->once()
            ->andReturn('identity');

        $mockResponse = m::mock(Response::class);
        $mockResponse->shouldReceive('isOk')
            ->once()
            ->andReturnTrue();
        $mockResponse->shouldReceive('getResult')
            ->once()
            ->andReturn([
                'flags' => [
                    'code' => ChangeExpiredPasswordResult::FAILURE_CLIENT_ERROR,
                    'messages' => [
                        'message1',
                        'message2'
                    ]
                ]
            ]);

        $this->commandSender->shouldReceive('send')
            ->andReturn($mockResponse);

        /** @var Request $request */
        $request = $this->sut->getRequest();
        $request->setMethod('POST');
        $request->setPost(new Parameters($post));

        $this->expectException(RuntimeException::class);

        $this->sut->indexAction();
    }
}
