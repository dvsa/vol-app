<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Form\Form;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Query\MyAccount\MyAccount as ItemDto;
use Dvsa\Olcs\Transfer\Command\MyAccount\UpdateMyAccountSelfserve as UpdateDto;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Element;
use Laminas\Form\ElementInterface;
use Laminas\Http\Request;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;
use Olcs\Controller\MyDetailsController as Sut;
use ReflectionClass;
use LmcRbacMvc\Service\AuthorizationService;

class MyDetailsControllerTest extends TestCase
{
    public $mockniTextTranslationUtil;
    public $mockauthService;
    public $mockflashMessengerHelper;
    public $mockscriptFactory;
    public $mockformHelper;
    protected $sut;
    protected $sm;

    public function setUp(): void
    {
        $this->sut = m::mock(Sut::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockniTextTranslationUtil = m::mock(NiTextTranslation::class)->makePartial();
        $this->mockauthService = m::mock(AuthorizationService::class)->makePartial();
        $this->mockflashMessengerHelper = m::mock(FlashMessengerHelperService::class)->makePartial();
        $this->mockscriptFactory = m::mock(ScriptFactory::class)->makePartial();
        $this->mockformHelper = m::mock(FormHelperService::class)->makePartial();

        $reflectionClass = new ReflectionClass(Sut::class);
        $this->setMockedProperties($reflectionClass, 'niTextTranslationUtil', $this->mockniTextTranslationUtil);
        $this->setMockedProperties($reflectionClass, 'authService', $this->mockauthService);
        $this->setMockedProperties($reflectionClass, 'flashMessengerHelper', $this->mockflashMessengerHelper);
        $this->setMockedProperties($reflectionClass, 'scriptFactory', $this->mockscriptFactory);
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

    public function testEditActionForGet(): void
    {
        $rawEditData = [
            'id' => 3,
            'version' => 1,
            'loginId' => 'stevefox',
            'contactDetails' => [
                'emailAddress' => 'steve@example.com',
                'id' => 106,
                'version' => 1,
                'person' => [
                    'familyName' => 'Fox',
                    'forename' => 'Steve',
                    'id' => 82,
                    'version' => 1,
                ],
            ],
            'translateToWelsh' => 'Y',
        ];
        $formattedData = [
            'main' => [
                'id' => 3,
                'version' => 1,
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('isPost')->andReturn(false);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($rawEditData);
        $this->sut->shouldReceive('handleQuery')->with(m::type(ItemDto::class))->andReturn($response);

        $mockFieldSet = m::mock(ElementInterface::class);
        $mockElementForename = m::mock(Element::class);
        $mockFieldSet->shouldReceive('get')->with('forename')->once()->andReturn($mockElementForename);
        $mockElementFamilyName = m::mock(Element::class);
        $mockFieldSet->shouldReceive('get')->with('familyName')->once()->andReturn($mockElementFamilyName);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->with($formattedData)->once();
        $mockForm->shouldReceive('get')->with('main')->andReturn($mockFieldSet)->once();

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->mockformHelper
            ->shouldReceive('lockElement')
            ->with($mockElementForename, 'name-change.locked.tooltip.message')
            ->once();
        $this->mockformHelper
            ->shouldReceive('lockElement')
            ->with($mockElementFamilyName, 'name-change.locked.tooltip.message')
            ->once();

        $this->mockscriptFactory
            ->shouldReceive('loadFile')
            ->with('my-details')
            ->once();

        $view = $this->sut->editAction();

        $this->assertInstanceOf(Form::class, $view->getVariable('form'));
    }

    public function testEditActionForGetWithError(): void
    {
        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('isPost')->andReturn(false);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);
        $this->sut->shouldReceive('handleQuery')->with(m::type(ItemDto::class))->andReturn($response);

        $mockFieldSet = m::mock(ElementInterface::class);
        $mockElementForename = m::mock(Element::class);
        $mockFieldSet->shouldReceive('get')->with('forename')->once()->andReturn($mockElementForename);
        $mockElementFamilyName = m::mock(Element::class);
        $mockFieldSet->shouldReceive('get')->with('familyName')->once()->andReturn($mockElementFamilyName);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->never();
        $mockForm->shouldReceive('get')->with('main')->andReturn($mockFieldSet)->once();

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->mockformHelper
            ->shouldReceive('lockElement')
            ->with($mockElementForename, 'name-change.locked.tooltip.message')
            ->once();
        $this->mockformHelper
            ->shouldReceive('lockElement')
            ->with($mockElementFamilyName, 'name-change.locked.tooltip.message')
            ->once();

        $this->mockflashMessengerHelper
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('unknown-error');

        $this->mockscriptFactory
            ->shouldReceive('loadFile')
            ->with('my-details')
            ->once();

        $view = $this->sut->editAction();

        $this->assertInstanceOf(Form::class, $view->getVariable('form'));
    }

    public function testEditActionForPost(): void
    {
        $rawEditData = [
            'id' => 3,
            'version' => 1,
            'loginId' => 'stevefox',
            'contactDetails' => [
                'emailAddress' => 'steve@example.com',
                'id' => 106,
                'version' => 1,
                'person' => [
                    'familyName' => 'Fox',
                    'forename' => 'Steve',
                    'id' => 82,
                    'version' => 1,
                ],
            ],
            'translateToWelsh' => 'Y',
        ];

        $postData = [
            'main' => [
                'id' => 3,
                'version' => 1,
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($rawEditData);
        $this->sut->shouldReceive('handleQuery')->with(m::type(ItemDto::class))->andReturn($response);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->twice()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $this->sut->shouldReceive('handleCommand')->with(m::type(UpdateDto::class))->andReturn($response);

        $this->mockflashMessengerHelper
            ->shouldReceive('addSuccessMessage')
            ->once()
            ->with('generic.updated.success');

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('your-account', ['action' => 'edit'], [], false)
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->editAction());
    }

    public function testEditActionForPostWithError(): void
    {
        $rawEditData = [
            'id' => 3,
            'version' => 1,
            'loginId' => 'stevefox',
            'contactDetails' => [
                'emailAddress' => 'steve@example.com',
                'id' => 106,
                'version' => 1,
                'person' => [
                    'familyName' => 'Fox',
                    'forename' => 'Steve',
                    'id' => 82,
                    'version' => 1,
                ],
            ],
            'translateToWelsh' => 'Y',
        ];

        $postData = [
            'main' => [
                'id' => 3,
                'version' => 1,
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock();
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($rawEditData);
        $this->sut->shouldReceive('handleQuery')->with(m::type(ItemDto::class))->andReturn($response);

        $mockFieldSet = m::mock(ElementInterface::class);
        $mockElementForename = m::mock(Element::class);
        $mockFieldSet->shouldReceive('get')->with('forename')->once()->andReturn($mockElementForename);
        $mockElementFamilyName = m::mock(Element::class);
        $mockFieldSet->shouldReceive('get')->with('familyName')->once()->andReturn($mockElementFamilyName);

        $mockForm = m::mock(\Common\Form\Form::class);
        $mockForm->shouldReceive('setData')->twice()->with($postData);
        $mockForm->shouldReceive('isValid')->once()->andReturn(true);
        $mockForm->shouldReceive('getData')->once()->andReturn($postData);
        $mockForm->shouldReceive('get')->with('main')->andReturn($mockFieldSet)->once();

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);
        $this->mockformHelper
            ->shouldReceive('lockElement')
            ->with($mockElementForename, 'name-change.locked.tooltip.message')
            ->once();
        $this->mockformHelper
            ->shouldReceive('lockElement')
            ->with($mockElementFamilyName, 'name-change.locked.tooltip.message')
            ->once();

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(false);

        $this->mockflashMessengerHelper
            ->shouldReceive('addErrorMessage')
            ->once()
            ->with('unknown-error');

        $mockParams = m::mock();
        $mockParams->shouldReceive('fromPost')->once()->andReturn($postData);
        $this->sut->shouldReceive('params')->once()->andReturn($mockParams);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(false);

        $this->sut->shouldReceive('handleCommand')->with(m::type(UpdateDto::class))->andReturn($response);

        $this->mockscriptFactory
            ->shouldReceive('loadFile')
            ->with('my-details')
            ->once();

        $view = $this->sut->editAction();

        $this->assertInstanceOf(Form::class, $view->getVariable('form'));
    }

    public function testEditActionForPostWithCancel(): void
    {
        $rawEditData = [
            'id' => 3,
            'version' => 1,
            'loginId' => 'stevefox',
            'contactDetails' => [
                'emailAddress' => 'steve@example.com',
                'id' => 106,
                'version' => 1,
                'person' => [
                    'familyName' => 'Fox',
                    'forename' => 'Steve',
                    'id' => 82,
                    'version' => 1,
                ],
            ],
            'translateToWelsh' => 'Y',
        ];

        $responseData = [
            'main' => [
                'id' => 3,
                'version' => 1,
                'loginId' => 'stevefox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'translateToWelsh' => 'Y',
            ]
        ];

        $mockRequest = m::mock(Request::class);
        $mockRequest->shouldReceive('isPost')->andReturn(true);
        $this->sut->shouldReceive('getRequest')->andReturn($mockRequest);

        $response = m::mock('stdClass');
        $response->shouldReceive('isOk')->andReturn(true);
        $response->shouldReceive('getResult')->andReturn($rawEditData);
        $this->sut->shouldReceive('handleQuery')->with(m::type(ItemDto::class))->andReturn($response);

        $mockForm = m::mock(Form::class);
        $mockForm->shouldReceive('setData')->once()->with($responseData);

        $this->mockformHelper
            ->shouldReceive('createFormWithRequest')
            ->with('MyDetails', $mockRequest)
            ->once()
            ->andReturn($mockForm);

        $this->sut->shouldReceive('isButtonPressed')->with('cancel')->once()->andReturn(true);

        $this->sut->shouldReceive('redirect->toRoute')
            ->with('your-account', ['action' => 'edit'], [], false)
            ->once()
            ->andReturn('REDIRECT');

        $this->assertEquals('REDIRECT', $this->sut->editAction());
    }
}
