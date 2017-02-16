<?php

namespace OlcsTest\Controller;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Transfer\Query as TransferQry;

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
    private $mockFlashMsgr;

    public function setUp()
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
        $this->mockForm->shouldReceive('get')->with('main')->andReturnSelf();
        $this->mockForm->shouldReceive('get')->with('permission')->andReturnSelf();
        $this->mockSl->shouldReceive('get')->with('Helper\Form')->andReturn($this->mockForm);

        $this->mockFlashMsgr = m::mock('stdClass');
        $this->mockSl->shouldReceive('get')->with('Helper\FlashMessenger')->andReturn($this->mockFlashMsgr);
    }

    public function tearDown()
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

        $this->mockRequest->shouldReceive('isPost')->andReturn(false); // false NOT to simulate form submission

        $this->mockParams->shouldReceive('fromRoute')->with('id', null)->andReturn($id);

        $this->mockFlashMsgr->shouldReceive('addSuccessMessage')->andReturnNull();

        $this->mockForm
            ->shouldReceive('createFormWithRequest')->with('User', $this->mockRequest)->andReturnSelf()
            ->shouldReceive('setData')->with($this->sut->formatLoadData($rawEditData))// happy path.
            ->shouldReceive('unsetValueOption')->with('tm')->once();

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

        $this->mockRequest->shouldReceive('isPost')->andReturn(true); // true to simulate form submission
        $this->mockRequest->shouldReceive('getPost')->andReturn($rawEditData);

        $this->mockParams->shouldReceive('fromPost')->withNoArgs()->andReturn($rawEditData);
        $this->mockParams->shouldReceive('fromRoute')->andReturnNull();

        $mockRedirect = m::mock(\Zend\Mvc\Controller\Plugin\Redirect::class);
        $mockRedirect
            ->shouldReceive('toRouteAjax')
            ->with('manage-user', ['action' => 'index'], [], false)
            ->andReturn('EXPECT');
        $this->sut->shouldReceive('redirect')->andReturn($mockRedirect);

        $this->mockFlashMsgr->shouldReceive('addSuccessMessage')->andReturnNull();

        $this->mockForm->shouldReceive('createFormWithRequest')->with('User', $this->mockRequest)->andReturnSelf();
        $this->mockForm->shouldReceive('isValid')->andReturn(true);
        $this->mockForm->shouldReceive('setData')->with($rawEditData);
        $this->mockForm->shouldReceive('getData')->andReturn($rawEditData);

        $this->assertEquals('EXPECT', $this->sut->editAction());
    }

    public function testDeleteActionCheckHimself()
    {
        $userId = 9999;

        $this->mockParams->shouldReceive('fromRoute')->with('id', null)->andReturn($userId);

        $mockRedirect = m::mock(\Zend\Mvc\Controller\Plugin\Redirect::class);
        $mockRedirect
            ->shouldReceive('toRouteAjax')
            ->with('manage-user', ['action' => 'index'], [], false)
            ->andReturn('EXPECT');
        $this->sut->shouldReceive('redirect')->andReturn($mockRedirect);

        $this->sut->shouldReceive('getCurrentUser')->once()->andReturn(['id' => $userId]);

        $this->assertEquals('EXPECT', $this->sut->deleteAction());
    }
}
