<?php

namespace OlcsTest\Controller;

use Dvsa\Olcs\Transfer\Command\User\CreateUserSelfserve;
use Dvsa\Olcs\Transfer\Command\User\UpdateUserSelfserve;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Transfer\Query as TransferQry;
use Zend\Mvc\Controller\Plugin\Redirect;

/**
 * Class User Controller Test
 *
 * @covers \Olcs\Controller\UserController
 */
class UserControllerTest extends MockeryTestCase
{
    /** @var  \Olcs\Controller\UserController | m\MockInterface */
    private $sut;

    /** @var  m\MockInterface */
    private $mockParams;
    /** @var  m\MockInterface */
    private $mockSl;
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
        $this->sut = m::mock(\Olcs\Controller\UserController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRequest = m::mock(\Zend\Http\Request::class);
        $this->sut->shouldReceive('getRequest')->andReturn($this->mockRequest);

        $this->mockResponse = m::mock('stdClass');
        $this->sut->shouldReceive('handleCommand')->andReturn($this->mockResponse);

        $this->mockParams = m::mock(\Zend\Mvc\Controller\Plugin\Params::class);
        $this->sut->shouldReceive('params')->andReturn($this->mockParams);

        $this->mockSl = m::mock(\Zend\ServiceManager\ServiceManager::class);
        $this->sut->shouldReceive('getServiceLocator')->andReturn($this->mockSl);

        $this->mockForm = m::mock(\Common\Form\Form::class);
        $this->mockForm->shouldReceive('get')->with('permission')->andReturnSelf();

        $this->mockFormHelper = m::mock();
        $this->mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($this->mockFormHelper);

        $this->mockFlashMsgr = m::mock('stdClass');
        $this->mockSl->shouldReceive('get')->with('Helper\FlashMessenger')->andReturn($this->mockFlashMsgr);

        $this->mockTranslator = m::mock();
        $this->mockTranslator->shouldReceive('translate')->andReturnUsing(
            function ($arg) {
                return $arg . "_translated";
            }
        );
        $this->mockSl->shouldReceive('get')->with('Helper\Translation')->andReturn($this->mockTranslator);

        $this->mockGuidanceHelper = m::mock();
        $this->mockGuidanceHelper->shouldReceive('append');
        $this->mockSl->shouldReceive('get')->with('Helper\Guidance')->andReturn($this->mockGuidanceHelper);
    }

    public function tearDown(): void
    {
        m::close();
    }

    public function testIndexAction()
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

        $mockUrl = m::mock(\Common\Service\Helper\UrlHelperService::class);
        $paramsArr['url'] = $mockUrl;

        $mockTable = m::mock(\Common\Service\Table\TableBuilder::class);
        $mockTable->shouldReceive('buildTable')->with('users', $data, $paramsArr, false)->andReturnSelf();

        $mockScript = m::mock('stdClass');
        $mockScript->shouldReceive('loadFiles')->once()->with(['lva-crud'])->andReturnNull();

        $this->mockSl->shouldReceive('get')->with('Helper\Url')->andReturn($mockUrl);
        $this->mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $this->mockSl->shouldReceive('get')->with('Script')->andReturn($mockScript);

        $actual = $this->sut->indexAction();

        $this->assertInstanceOf(\Olcs\View\Model\User::class, $actual);
        $this->assertEquals($mockTable, $actual->getVariable('users'));
    }

    public function testIndexActionNotOk()
    {
        $this->mockParams->shouldReceive('fromQuery')->andReturnSelf();

        $this->mockRequest->shouldReceive('isPost')->andReturn(false);

        $this->mockResponse->shouldReceive('isOk')->andReturn(false);
        $this->sut->shouldReceive('handleQuery')->andReturn($this->mockResponse);

        $this->mockFlashMsgr->shouldReceive('addUnknownError')->once();

        $mockUrl = m::mock(\Common\Service\Helper\UrlHelperService::class);

        $mockTable = m::mock(\Common\Service\Table\TableBuilder::class)->makePartial();
        $mockTable->shouldReceive('buildTable')->once()->andReturnSelf();

        $mockScript = m::mock('stdClass');
        $mockScript->shouldReceive('loadFiles')->once()->with(['lva-crud'])->andReturnNull();

        $this->mockSl->shouldReceive('get')->with('Helper\Url')->andReturn($mockUrl);
        $this->mockSl->shouldReceive('get')->with('Table')->andReturn($mockTable);
        $this->mockSl->shouldReceive('get')->with('Script')->andReturn($mockScript);

        $actual = $this->sut->indexAction();

        $this->assertInstanceOf(\Olcs\View\Model\User::class, $actual);
        $this->assertEquals($mockTable, $actual->getVariable('users'));
    }

    public function testSaveExistingRecord()
    {
        $rawEditData = array(
            'id' => 3,
            'version' => 1,
            'loginId' => 'stevefox',
            'contactDetails' => array(
                'emailAddress' => 'steve@example.com',
                'id' => 106,
                'version' => 1,
                'person' => array(
                    'familyName' => 'Fox',
                    'forename' => 'Steve',
                    'id' => 82,
                    'version' => 1,
                ),
            ),
            'permission' => 'user',
            'translateToWelsh' => 'Y',
        );

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

        $this->mockFlashMsgr->shouldReceive('addSuccessMessage')->andReturnNull();

        $this->mockForm->shouldReceive('setData')->with($this->sut->formatLoadData($rawEditData))// happy path.
            ->shouldReceive('unsetValueOption')->with('tm')->once()
            ->shouldReceive('get')->with('main')->andReturnSelf();

        $this->mockFormHelper
            ->shouldReceive('createFormWithRequest')->with('User', $this->mockRequest)->andReturn($this->mockForm);

        $view = $this->sut->editAction();

        $this->assertInstanceOf(\Common\Form\Form::class, $view->getVariable('form'));
    }

    public function testSaveWithPostData()
    {
        $rawEditData = array(
            'main' => array(
                'loginId' => 'stevefox',
                'forename' => 'Steve',
                'familyName' => 'Fox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'permission' => 'admin',
                'id' => '3',
                'version' => '1',
                'translateToWelsh' => 'Y',
            ),
        );

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

        $this->mockFlashMsgr->shouldReceive('addSuccessMessage')->andReturnNull();

        $this->mockForm->shouldReceive('isValid')->andReturn(true);
        $this->mockForm->shouldReceive('setData')->with($rawEditData);
        $this->mockForm->shouldReceive('getData')->andReturn($rawEditData);

        $this->mockFormHelper->shouldReceive('createFormWithRequest')->with('User', $this->mockRequest)->andReturn($this->mockForm);

        $this->assertEquals('EXPECT', $this->sut->editAction());
    }

    public function testAddAction()
    {
        $rawEditData = array(
            'main' => array(
                'loginId' => 'stevefox',
                'forename' => 'Steve',
                'familyName' => 'Fox',
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'permission' => 'admin',
                'version' => '1',
                'translateToWelsh' => 'Y',
            ),
        );

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

        $this->mockFlashMsgr->shouldReceive('addSuccessMessage')->andReturnNull();

        $this->mockForm->shouldReceive('isValid')->andReturn(true);
        $this->mockForm->shouldReceive('setData')->with($rawEditData);
        $this->mockForm->shouldReceive('getData')->andReturn($rawEditData);

        $this->mockFormHelper->shouldReceive('createFormWithRequest')->with('User', $this->mockRequest)->andReturn($this->mockForm);

        $this->assertEquals('EXPECT', $this->sut->addAction());
    }

    public function testDeleteActionCheckHimself()
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

    public function testSaveExistingRecordLocksNameFields()
    {
        $rawEditData = array(
            'id' => 3,
            'version' => 1,
            'loginId' => 'stevefox',
            'contactDetails' => array(
                'emailAddress' => 'steve@example.com',
                'id' => 106,
                'version' => 1,
                'person' => array(
                    'familyName' => 'Fox',
                    'forename' => 'Steve',
                    'id' => 82,
                    'version' => 1,
                ),
            ),
            'permission' => 'user',
            'translateToWelsh' => 'Y',
        );

        $id = 3;

        $this->mockResponse
            ->shouldReceive('isOk')->andReturn(true)
            ->shouldReceive('getResult')->andReturn($rawEditData);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(TransferQry\User\UserSelfserve::class))
            ->andReturn($this->mockResponse);

        $this->mockRequest->shouldReceive('isPost')->andReturn(false); // false NOT to simulate form submission

        $this->mockParams->shouldReceive('fromRoute')->with('id', null)->andReturn($id);

        $this->mockFlashMsgr->shouldReceive('addSuccessMessage')->andReturnNull();

        $mockFieldSet = m::mock();
        $mockElementForename = m::mock();
        $mockFieldSet->shouldReceive('get')->with('forename')->once()->andReturn($mockElementForename);
        $mockElementFamilyName = m::mock();
        $mockFieldSet->shouldReceive('get')->with('familyName')->once()->andReturn($mockElementFamilyName);
        $mockPermissionElement = m::mock();
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

        $view = $this->sut->editAction();

        $this->assertInstanceOf(\Common\Form\Form::class, $view->getVariable('form'));
    }

    public function testSaveGetsInvalidResponseAndRedirectsToIndex()
    {
        $id = 3;

        $this->mockResponse
            ->shouldReceive('isOk')->andReturn(false);

        $this->sut->shouldReceive('handleQuery')
            ->with(m::type(TransferQry\User\UserSelfserve::class))
            ->andReturn($this->mockResponse);

        $this->mockFlashMsgr->shouldReceive('addUnknownError')->once();

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
