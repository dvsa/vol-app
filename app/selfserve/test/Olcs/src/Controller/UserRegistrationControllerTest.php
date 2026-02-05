<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Helper\UrlHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\User\RegisterUserSelfserve as CreateDto;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceRegisteredAddress as LicenceByNumberDto;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\Mapper\CreateAccountMapper;
use Olcs\Controller\UserRegistrationController as Sut;
use Olcs\Session\ConsultantRegistration;
use ReflectionClass;
use LmcRbacMvc\Service\AuthorizationService;

class UserRegistrationControllerTest extends TestCase
{
    protected $sut;
    protected $sm;

    private $mockTranslationHelper;

    private $mockniTextTranslationUtil;

    private $mockauthService;

    private $mockFlashMessengerHelper;

    private $mockFormHelper;

    private $mockScriptFactory;

    private $mockUrlHelper;

    private $mockFormatSaveDataMapper;

    private $mockConsultantRegistrationSession;


    public function setUp(): void
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockniTextTranslationUtil = m::mock(NiTextTranslation::class)->makePartial();
        $this->mockauthService = m::mock(AuthorizationService::class)->makePartial();
        $this->mockFormHelper = m::mock(FormHelperService::class)->makePartial();
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class)->makePartial();
        $this->mockScriptFactory = m::mock(ScriptFactory::class)->makePartial();
        $this->mockUrlHelper = m::mock(UrlHelperService::class)->makePartial();
        $this->mockTranslationHelper = m::mock(TranslationHelperService::class)->makePartial();
        $this->mockFormatSaveDataMapper = m::mock(CreateAccountMapper::class)->makePartial();
        $this->mockConsultantRegistrationSession = m::mock(ConsultantRegistration::class)->makePartial();

        $this->mockConsultantRegistrationSession->shouldReceive('getOperatorAdmin')->andReturn(true);

        $reflectionClass = new ReflectionClass(Sut::class);
        $this->setMockedProperties($reflectionClass, 'niTextTranslationUtil', $this->mockniTextTranslationUtil);
        $this->setMockedProperties($reflectionClass, 'authService', $this->mockauthService);
        $this->setMockedProperties($reflectionClass, 'flashMessengerHelper', $this->mockFlashMessengerHelper);
        $this->setMockedProperties($reflectionClass, 'formHelper', $this->mockFormHelper);
        $this->setMockedProperties($reflectionClass, 'scriptFactory', $this->mockScriptFactory);
        $this->setMockedProperties($reflectionClass, 'urlHelper', $this->mockUrlHelper);
        $this->setMockedProperties($reflectionClass, 'translationHelper', $this->mockTranslationHelper);
        $this->setMockedProperties($reflectionClass, 'formatDataMapper', $this->mockFormatSaveDataMapper);
        $this->setMockedProperties($reflectionClass, 'consultantRegistrationSession', $this->mockConsultantRegistrationSession);
    }

    /**
     * @psalm-param ReflectionClass<Sut> $reflectionClass
     */
    public function setMockedProperties(ReflectionClass $reflectionClass, string $property, m\LegacyMockInterface $value): void
    {
        $reflectionProperty = $reflectionClass->getProperty($property);
        $reflectionProperty->setValue($this->sut, $value);
    }

    public function testAddActionForGet(): void
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(false);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $termsAgreedElement = new \Laminas\Form\Element();
        $termsAgreedElement->setLabel('termsAgreedLabel');

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm
            ->shouldReceive('get')
            ->with('fields')
            ->once()
            ->andReturnSelf()
            ->shouldReceive('get')
            ->with('termsAgreed')
            ->once()
            ->andReturn($termsAgreedElement);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->mockTranslationHelper
            ->shouldReceive('translateReplace')
            ->with('termsAgreedLabel', ['URL'])
            ->once();

        $this->mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with('terms-and-conditions')
            ->once()
            ->andReturn('URL');

        $this->mockScriptFactory
            ->shouldReceive('loadFile')
            ->with('user-registration')
            ->once();

        $placeholder = m::mock();
        $placeholder->shouldReceive('setPlaceholder')
            ->with('pageTitle', 'page.title.user-registration.add')
            ->once();
        $this->sut->shouldReceive('placeholder')->andReturn($placeholder);

        $view = $this->sut->addAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
    }

    public function testAddActionForPostWithCancel(): void
    {
        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('index')
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->addAction());
    }

    public function testAddActionForPostWithOrg(): void
    {
        $postData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'N',
                'organisationName' => 'Org name',
                'businessType' => 'type',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->once()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);
        $this->sut->shouldReceive('isButtonPressed')->with('postAccount')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $this->sut->shouldReceive('handleCommand')->with(m::type(CreateDto::class))->andReturn($response);

        $placeholder = m::mock();
        $placeholder->shouldReceive('setPlaceholder')
            ->with('pageTitle', 'user-registration.page.check-email.title')
            ->once();
        $this->sut->shouldReceive('placeholder')->andReturn($placeholder);

        $this->mockFormatSaveDataMapper->shouldReceive('formatSaveData')->once()->with($postData)->andReturn($postData);
        $this->mockFormatSaveDataMapper->shouldReceive('formatPostData')->once()->with($postData)->andReturn($postData);

        $view = $this->sut->addAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('olcs/user-registration/check-email', $view->getTemplate());
    }

    public function testAddActionForPostWithOrgError(): void
    {
        $postData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'N',
                'organisationName' => 'Org name',
                'businessType' => 'type',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $termsAgreedElement = new \Laminas\Form\Element();
        $termsAgreedElement->setLabel('termsAgreedLabel');

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->twice()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);
        $mockForm->shouldReceive('get')->once()->with('fields')->andReturnSelf();
        $mockForm->shouldReceive('get')->once()->with('termsAgreed')->andReturn($termsAgreedElement);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->twice()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);
        $this->sut->shouldReceive('isButtonPressed')->with('postAccount')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $response->shouldReceive('getResult')->andReturn([]);
        $this->sut->shouldReceive('handleCommand')->with(m::type(CreateDto::class))->andReturn($response);

        $this->mockFlashMessengerHelper
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('unknown-error');

        $this->mockTranslationHelper
            ->shouldReceive('translateReplace')
            ->with('termsAgreedLabel', ['URL'])
            ->once();

        $this->mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with('terms-and-conditions')
            ->once()
            ->andReturn('URL');

        $this->mockScriptFactory
            ->shouldReceive('loadFile')
            ->with('user-registration')
            ->once();

        $this->mockFormatSaveDataMapper->shouldReceive('formatSaveData')->once()->with($postData)->andReturn($postData);
        $this->mockFormatSaveDataMapper->shouldReceive('formatPostData')->once()->with($postData)->andReturn($postData);

        $view = $this->sut->addAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
    }

    public function testAddActionForPostWithLic(): void
    {
        $postData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo',
                'translateToWelsh' => 'Y',
            ]
        ];

        $formattedPostData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo',
                'translateToWelsh' => 'Y',
                'businessType' => null,
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->once()->with($formattedPostData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($formattedPostData);

        $mockFormAddress = m::mock(\Common\Form\Form::class);
        $mockFormAddress->shouldReceive('setData')->once()->with($formattedPostData);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistrationAddress', $mockRequest)
            ->once()
            ->andReturn($mockFormAddress);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);
        $this->sut->shouldReceive('isButtonPressed')->with('postAccount')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $licData = [
            'correspondenceCd' => [
                'address' => ['address']
            ],
            'organisation' => [
                'name' => ['org name']
            ],
        ];
        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($licData);
        $this->sut->shouldReceive('handleQuery')->with(m::type(LicenceByNumberDto::class))->andReturn($response);

        $placeholder = m::mock();
        $placeholder->shouldReceive('setPlaceholder')
            ->with('pageTitle', 'user-registration.page.check-details.title')
            ->once();
        $this->sut->shouldReceive('placeholder')->andReturn($placeholder);

        $this->mockFormatSaveDataMapper->shouldReceive('formatPostData')->once()->with($postData)->andReturn($formattedPostData);

        $view = $this->sut->addAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('olcs/user-registration/check-details', $view->getTemplate());
    }

    public function testAddActionForPostWithLicError(): void
    {
        $postData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo',
                'translateToWelsh' => 'Y',
            ]
        ];

        $formattedPostData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo',
                'translateToWelsh' => 'Y',
                'businessType' => null,
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $termsAgreedElement = new \Laminas\Form\Element();
        $termsAgreedElement->setLabel('termsAgreedLabel');

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->twice()->with($formattedPostData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($formattedPostData);
        $mockForm->shouldReceive('setMessages')->once()->with(m::type('array'));
        $mockForm->shouldReceive('get')->once()->with('fields')->andReturnSelf();
        $mockForm->shouldReceive('get')->once()->with('termsAgreed')->andReturn($termsAgreedElement);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->twice()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);
        $this->sut->shouldReceive('isButtonPressed')->with('postAccount')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $response->shouldReceive('isNotFound')->andReturn(false);
        $response->shouldReceive('getResult')->andReturn(['messages' => ['licenceNumber' => 'err']]);
        $this->sut->shouldReceive('handleQuery')->with(m::type(LicenceByNumberDto::class))->andReturn($response);

        $this->mockTranslationHelper
            ->shouldReceive('translateReplace')
            ->with('termsAgreedLabel', ['URL'])
            ->once();

        $this->mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with('terms-and-conditions')
            ->once()
            ->andReturn('URL');

        $this->mockScriptFactory
            ->shouldReceive('loadFile')
            ->with('user-registration')
            ->once();

        $this->mockFormatSaveDataMapper->shouldReceive('formatPostData')->once()->with($postData)->andReturn($formattedPostData);

        $view = $this->sut->addAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
    }

    public function testAddActionForPostWithLicConfirmed(): void
    {
        $postData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo',
                'translateToWelsh' => 'Y',
            ]
        ];

        $formattedPostData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo',
                'translateToWelsh' => 'Y',
                'businessType' => null,
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->once()->with($formattedPostData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($formattedPostData);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);
        $this->sut->shouldReceive('isButtonPressed')->with('postAccount')->once()->andReturn(true);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $this->sut->shouldReceive('handleCommand')->with(m::type(CreateDto::class))->andReturn($response);

        $placeholder = m::mock();
        $placeholder->shouldReceive('setPlaceholder')
            ->with('pageTitle', 'user-registration.page.account-created.title')
            ->once();
        $this->sut->shouldReceive('placeholder')->andReturn($placeholder);

        $this->mockFormatSaveDataMapper->shouldReceive('formatSaveData')->once()->with($formattedPostData)->andReturn($formattedPostData);
        $this->mockFormatSaveDataMapper->shouldReceive('formatPostData')->once()->with($postData)->andReturn($formattedPostData);

        $view = $this->sut->addAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
        $this->assertEquals('olcs/user-registration/account-created', $view->getTemplate());
    }

    public function testAddActionForPostWithLicConfirmedError(): void
    {
        $postData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo',
                'translateToWelsh' => 'Y',
            ]
        ];

        $formattedPostData = [
            'fields' => [
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'isLicenceHolder' => 'Y',
                'licenceNumber' => 'licNo',
                'translateToWelsh' => 'Y',
                'businessType' => null,
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $termsAgreedElement = new \Laminas\Form\Element();
        $termsAgreedElement->setLabel('termsAgreedLabel');

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->twice()->with($formattedPostData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($formattedPostData);
        $mockForm->shouldReceive('setMessages')->once()->with(m::type('array'));
        $mockForm->shouldReceive('get')->once()->with('fields')->andReturnSelf();
        $mockForm->shouldReceive('get')->once()->with('termsAgreed')->andReturn($termsAgreedElement);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('UserRegistration', $mockRequest)
            ->twice()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);
        $this->sut->shouldReceive('isButtonPressed')->with('postAccount')->once()->andReturn(true);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $response->shouldReceive('getResult')->andReturn(['messages' => ['loginId' => 'err']]);
        $this->sut->shouldReceive('handleCommand')->with(m::type(CreateDto::class))->andReturn($response);

        $this->mockTranslationHelper
            ->shouldReceive('translateReplace')
            ->with('termsAgreedLabel', ['URL'])
            ->once();

        $this->mockUrlHelper
            ->shouldReceive('fromRoute')
            ->with('terms-and-conditions')
            ->once()
            ->andReturn('URL');

        $this->mockScriptFactory
            ->shouldReceive('loadFile')
            ->with('user-registration')
            ->once();

        $this->mockFormatSaveDataMapper->shouldReceive('formatSaveData')->once()->with($formattedPostData)->andReturn($formattedPostData);
        $this->mockFormatSaveDataMapper->shouldReceive('formatPostData')->once()->with($postData)->andReturn($formattedPostData);

        $view = $this->sut->addAction();

        $this->assertInstanceOf(\Laminas\View\Model\ViewModel::class, $view);
    }
}
