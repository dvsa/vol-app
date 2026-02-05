<?php

declare(strict_types=1);

namespace OlcsTest\Controller;

use Common\Form\Form;
use Common\Service\Cqrs\Response;
use Common\Service\Helper\FlashMessengerHelperService;
use Common\Service\Helper\FormHelperService;
use Common\Service\Helper\GuidanceHelperService;
use Common\Service\Helper\TranslationHelperService;
use Common\Service\Script\ScriptFactory;
use Dvsa\Olcs\Transfer\Command\User\CreateUserSelfserve;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserSelfserve;
use Dvsa\Olcs\Utils\Translation\NiTextTranslation;
use Laminas\Form\Element;
use Laminas\Form\ElementInterface;
use Laminas\Http\Request;
use Laminas\Mvc\Controller\Plugin\Params;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Laminas\Mvc\Controller\Plugin\Redirect;
use Olcs\Controller\UserController;
use Olcs\View\Model\User;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Class User Controller Test
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Controller\UserController::class)]
class UserControllerTest extends MockeryTestCase
{
    /**
     * @var (\Dvsa\Olcs\Utils\Translation\NiTextTranslation & \Mockery\MockInterface)
     */
    public $mockNiTextTranslationUtil;
    /**
     * @var (\LmcRbacMvc\Service\AuthorizationService & \Mockery\MockInterface)
     */
    public $mockAuthService;
    public $mockUser;
    public $mockScriptFactory;
    public $mockFlashMessengerHelper;
    public $mockTranslationHelper;
    /** @var  \Olcs\Controller\UserController | m\MockInterface */
    private $sut;

    /** @var  m\MockInterface */
    private $mockParams;
    /** @var  m\MockInterface */
    private $mockResponse;
    /** @var  m\MockInterface */
    private $mockRequest;

    /** @var  m\MockInterface */
    private $mockForm;

    /** @var  m\MockInterface */
    private $mockFormHelper;

    /** @var  m\MockInterface */
    private $mockFlashMsgr;

    /** @var  m\MockInterface */
    private $mockTranslator;

    /** @var  m\MockInterface */
    private $mockGuidanceHelper;

    public function setUp(): void
    {

        $this->mockNiTextTranslationUtil = m::mock(NiTextTranslation::class);
        $this->mockAuthService = m::mock(AuthorizationService::class);
        $this->mockUser = m::mock(User::class);
        $this->mockScriptFactory = m::mock(ScriptFactory::class);
        $this->mockFormHelper = m::mock(FormHelperService::class);
        $this->mockFlashMessengerHelper = m::mock(FlashMessengerHelperService::class);
        $this->mockTranslationHelper = m::mock(TranslationHelperService::class);
        $this->mockGuidanceHelper = m::mock(GuidanceHelperService::class);

        $this->sut = m::mock(UserController::class, [
            $this->mockNiTextTranslationUtil,
            $this->mockAuthService,
            $this->mockUser,
            $this->mockScriptFactory,
            $this->mockFormHelper,
            $this->mockFlashMessengerHelper,
            $this->mockTranslationHelper,
            $this->mockGuidanceHelper
        ])->shouldAllowMockingProtectedMethods()->makePartial();

        $this->mockRequest = m::mock(Request::class);
        $this->sut->shouldReceive('getRequest')->andReturn($this->mockRequest);

        $this->mockResponse = m::mock('stdClass');
        $this->sut->shouldReceive('handleCommand')->andReturn($this->mockResponse);

        $this->mockParams = m::mock(Params::class);
        $this->sut->shouldReceive('params')->andReturn($this->mockParams);

        $this->mockForm = m::mock(Form::class);
        $this->mockForm->shouldReceive('get')->with('permission')->andReturnSelf();

        $this->mockTranslationHelper->shouldReceive('translate')->andReturnUsing(
            fn($arg) => $arg . "_translated"
        );

        $this->mockGuidanceHelper->shouldReceive('append');
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testIndexAction(): void
    {
        $page = '2';
        $sort = 'name';
        $order = 'ASC';
        $limit = 20;
        $query = [];

        $paramsArr = [
            'page' => $page,
            'sort' => $sort,
            'order' => $order,
            'limit' => $limit,
            'query' => $query,
        ];

        $data = ['data'];

        $this->mockResponse
            ->shouldReceive('isOk')->andReturn(true)
            ->shouldReceive('getResult')->andReturn($data);

        $this->sut->shouldReceive('handleQuery')->andReturn($this->mockResponse);

        $this->mockParams
            ->shouldReceive('fromQuery')->with('page', 1)->andReturn($page)
            ->shouldReceive('fromQuery')->with('sort', 'id')->andReturn($sort)
            ->shouldReceive('fromQuery')->with('order', 'DESC')->andReturn($order)
            ->shouldReceive('fromQuery')->with('limit', 10)->andReturn($limit)
            ->shouldReceive('fromQuery')->withNoArgs()->andReturn($query);

        $this->mockRequest->shouldReceive('isPost')->andReturn(false);

        $this->mockUser->expects('setUsers')->with($data, $paramsArr);

        $this->mockScriptFactory->shouldReceive('loadFiles')->once()->with(['lva-crud'])->andReturnNull();

        $actual = $this->sut->indexAction();

        $this->assertInstanceOf(User::class, $actual);
    }

    public function testIndexActionNotOk(): void
    {
        $page = '2';
        $sort = 'name';
        $order = 'ASC';
        $limit = 20;
        $query = [];

        $paramsArr = [
            'page' => $page,
            'sort' => $sort,
            'order' => $order,
            'limit' => $limit,
            'query' => $query,
        ];

        $this->mockParams
            ->shouldReceive('fromQuery')->with('page', 1)->andReturn($page)
            ->shouldReceive('fromQuery')->with('sort', 'id')->andReturn($sort)
            ->shouldReceive('fromQuery')->with('order', 'DESC')->andReturn($order)
            ->shouldReceive('fromQuery')->with('limit', 10)->andReturn($limit)
            ->shouldReceive('fromQuery')->withNoArgs()->andReturn($query);

        $this->mockRequest->shouldReceive('isPost')->andReturn(false);

        $this->mockResponse->shouldReceive('isOk')->andReturn(false);
        $this->sut->shouldReceive('handleQuery')->andReturn($this->mockResponse);

        $this->mockFlashMessengerHelper->shouldReceive('addUnknownError')->once();

        $this->mockUser->expects('setUsers')->with([], $paramsArr);

        $this->mockScriptFactory->shouldReceive('loadFiles')->once()->with(['lva-crud'])->andReturnNull();

        $actual = $this->sut->indexAction();

        $this->assertInstanceOf(User::class, $actual);
    }

    public function testSaveExistingRecord(): void
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
            'permission' => 'user',
            'translateToWelsh' => 'Y',
        ];

        $id = 3;

        $this->mockResponse
            ->shouldReceive('isOk')->andReturn(true)
            ->shouldReceive('getResult')->andReturn($rawEditData);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(TransferQry\User\UserSelfserve::class))
            ->andReturn($this->mockResponse);

        $this->sut->shouldReceive('lockNameFields')->once();

        $this->mockRequest->shouldReceive('isPost')->andReturn(false); // false NOT to simulate form submission

        $this->mockParams->shouldReceive('fromRoute')->with('id', null)->andReturn($id);

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')->andReturnNull();

        $this->mockForm->shouldReceive('setData')->with($this->sut->formatLoadData($rawEditData))// happy path.
            ->shouldReceive('unsetValueOption')->with('tm')->once()
            ->shouldReceive('get')->with('main')->andReturnSelf();

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')->with('User', $this->mockRequest)->andReturn($this->mockForm);

        $mockIsEnabledResponse = m::mock(Response::class);
        $mockIsEnabledResponse->shouldReceive('getResult')->andReturn([
            'isEnabled' => true,
        ]);
        $this->sut->shouldReceive('handleQuery')->with(m::type(TransferQry\FeatureToggle\IsEnabled::class))->andReturn($mockIsEnabledResponse);

        $view = $this->sut->editAction();

        $this->assertInstanceOf(Form::class, $view->getVariable('form'));
    }

    public function testSaveWithPostData(): void
    {
        $rawEditData = [
            'main' => [
                'loginId' => 'stevefox',
                'forename' => 'Steve',
                'familyName' => 'Fox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'permission' => 'admin',
                'id' => '3',
                'version' => '1',
                'translateToWelsh' => 'Y',
            ],
        ];

        $this->mockResponse->shouldReceive('isOk')->andReturn(true);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(UpdateUserSelfserve::class))
            ->andReturn($this->mockResponse);

        $this->mockRequest->shouldReceive('isPost')->andReturn(true); // true to simulate form submission
        $this->mockRequest->shouldReceive('getPost')->andReturn($rawEditData);

        $this->mockParams->shouldReceive('fromPost')->withNoArgs()->andReturn($rawEditData);
        $this->mockParams->shouldReceive('fromRoute')->andReturnNull();

        $mockRedirect = m::mock(Redirect::class);
        $mockRedirect
            ->shouldReceive('toRouteAjax')
            ->with('manage-user', ['action' => 'index'], [], false)
            ->andReturn('EXPECT');
        $this->sut->shouldReceive('redirect')->andReturn($mockRedirect);

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')->andReturnNull();

        $this->mockForm->shouldReceive('isValid')->andReturn(true);
        $this->mockForm->shouldReceive('setData')->with($rawEditData);
        $this->mockForm->shouldReceive('getData')->andReturn($rawEditData);

        $this->mockFormHelper->shouldReceive('createFormWithRequest')->with('User', $this->mockRequest)->andReturn($this->mockForm);

        $this->assertEquals('EXPECT', $this->sut->editAction());
    }

    public function testAddAction(): void
    {
        $rawEditData = [
            'main' => [
                'loginId' => 'stevefox',
                'forename' => 'Steve',
                'familyName' => 'Fox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'permission' => 'admin',
                'version' => '1',
                'translateToWelsh' => 'Y',
            ],
        ];

        $this->mockResponse->shouldReceive('isOk')->andReturn(true);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(CreateUserSelfserve::class))
            ->andReturn($this->mockResponse);

        $this->mockRequest->shouldReceive('isPost')->andReturn(true); // true to simulate form submission
        $this->mockRequest->shouldReceive('getPost')->andReturn($rawEditData);

        $this->mockParams->shouldReceive('fromPost')->withNoArgs()->andReturn($rawEditData);
        $this->mockParams->shouldReceive('fromRoute')->andReturnNull();

        $mockRedirect = m::mock(Redirect::class);
        $mockRedirect
            ->shouldReceive('toRouteAjax')
            ->with('manage-user', ['action' => 'index'], [], false)
            ->andReturn('EXPECT');
        $this->sut->shouldReceive('redirect')->andReturn($mockRedirect);

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')->andReturnNull();

        $this->mockForm->shouldReceive('isValid')->andReturn(true);
        $this->mockForm->shouldReceive('setData')->with($rawEditData);
        $this->mockForm->shouldReceive('getData')->andReturn($rawEditData);

        $this->mockFormHelper->shouldReceive('createFormWithRequest')->with('User', $this->mockRequest)->andReturn($this->mockForm);

        $this->assertEquals('EXPECT', $this->sut->addAction());
    }

    public function testDeleteActionCheckHimself(): void
    {
        $userId = 9999;

        $this->mockParams->shouldReceive('fromRoute')->with('id', null)->andReturn($userId);

        $mockRedirect = m::mock(Redirect::class);
        $mockRedirect
            ->shouldReceive('toRouteAjax')
            ->with('manage-user', ['action' => 'index'], [], false)
            ->andReturn('EXPECT');
        $this->sut->shouldReceive('redirect')->andReturn($mockRedirect);

        $this->sut->shouldReceive('getCurrentUser')->once()->andReturn(['id' => $userId]);

        $this->assertEquals('EXPECT', $this->sut->deleteAction());
    }

    public function testSaveExistingRecordLocksNameFields(): void
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
            'permission' => 'user',
            'translateToWelsh' => 'Y',
        ];

        $id = 3;

        $this->mockResponse
            ->shouldReceive('isOk')->andReturn(true)
            ->shouldReceive('getResult')->andReturn($rawEditData);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(TransferQry\User\UserSelfserve::class))
            ->andReturn($this->mockResponse);

        $this->mockRequest->shouldReceive('isPost')->andReturn(false); // false NOT to simulate form submission

        $this->mockParams->shouldReceive('fromRoute')->with('id', null)->andReturn($id);

        $this->mockFlashMessengerHelper->shouldReceive('addSuccessMessage')->andReturnNull();

        $mockFieldSet = m::mock(ElementInterface::class);
        $mockElementForename = m::mock(Element::class);
        $mockFieldSet->shouldReceive('get')->with('forename')->once()->andReturn($mockElementForename);
        $mockElementFamilyName = m::mock(Element::class);
        $mockFieldSet->shouldReceive('get')->with('familyName')->once()->andReturn($mockElementFamilyName);
        $mockPermissionElement = m::mock(Element::class);
        $mockPermissionElement->shouldReceive('unsetValueOption')->with('tm')->once();
        $mockFieldSet->shouldReceive('get')->with('permission')->once()->andReturn($mockPermissionElement);

        $this->mockForm
            ->shouldReceive('setData')->with($this->sut->formatLoadData($rawEditData))// happy path.
            ->shouldReceive('get')->with('main')->andReturn($mockFieldSet)
            ->shouldReceive('get')->with('permission')->andReturn(null);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('User', $this->mockRequest)
            ->andReturn($this->mockForm);
        $this->mockFormHelper
            ->shouldReceive('lockElement')
            ->with($mockElementForename, 'name-change.locked.tooltip.message')
            ->once();
        $this->mockFormHelper
            ->shouldReceive('lockElement')
            ->with($mockElementFamilyName, 'name-change.locked.tooltip.message')
            ->once();
        $this->mockFormHelper
            ->shouldReceive('disableElement')
            ->with($this->mockForm, 'main->forename')
            ->once();
        $this->mockFormHelper
            ->shouldReceive('disableElement')
            ->with($this->mockForm, 'main->familyName')
            ->once();
        $this->mockFormHelper
            ->shouldReceive('disableElement')
            ->with($this->mockForm, 'main->loginId')
            ->once();

        $mockIsEnabledResponse = m::mock(Response::class);
        $mockIsEnabledResponse->shouldReceive('getResult')->andReturn([
            'isEnabled' => true,
        ]);
        $this->sut->shouldReceive('handleQuery')->with(m::type(TransferQry\FeatureToggle\IsEnabled::class))->andReturn($mockIsEnabledResponse);

        $view = $this->sut->editAction();

        $this->assertInstanceOf(Form::class, $view->getVariable('form'));
    }

    public function testSaveGetsInvalidResponseAndRedirectsToIndex(): void
    {
        $id = 3;

        $this->mockResponse
            ->shouldReceive('isOk')->andReturn(false);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(TransferQry\User\UserSelfserve::class))
            ->andReturn($this->mockResponse);

        $this->mockFlashMessengerHelper->shouldReceive('addUnknownError')->once();

        $mockRedirect = m::mock(Redirect::class);
        $mockRedirect
            ->shouldReceive('toRouteAjax')
            ->with('manage-user', ['action' => 'index'], [], false)
            ->andReturn('EXPECT');
        $this->sut->shouldReceive('redirect')->andReturn($mockRedirect);

        $this->mockParams->shouldReceive('fromRoute')->with('id', null)->andReturn($id);

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')
            ->with('User', $this->mockRequest)
            ->andReturn($this->mockForm);

        $this->assertEquals('EXPECT', $this->sut->editAction());
    }
}
