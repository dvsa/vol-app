<?php
/**
 * Class User Controller Test
 *
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
namespace OlcsTest\Controller;

use Olcs\BusinessRule\Rule\UserMappingContactDetails;
use Olcs\Controller\UserController;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

/**
 * Class User Controller Test
 *
 * @package OlcsTest\Controller
 * @author Craig Reasbeck <craig.reasbeck@valtech.co.uk>
 */
class UserControllerTest extends TestCase
{
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
            'page'    => $page,
            'sort'    => $sort,
            'order'   => $order,
            'limit'   => $limit,
            'query'   => $query
        ];

        $table = ['table'];

        $sut = new UserController();

        $data = ['data'];

        $params = m::mock('\Zend\Mvc\Controller\Plugin\Params');
        $params->shouldReceive('fromQuery')->with('page', 1)->andReturn($page);
        $params->shouldReceive('fromQuery')->with('sort', 'id')->andReturn($sort);
        $params->shouldReceive('fromQuery')->with('order', 'DESC')->andReturn($order);
        $params->shouldReceive('fromQuery')->with('limit', 10)->andReturn($limit);
        $params->shouldReceive('fromQuery')->withNoArgs()->andReturn($query);

        $pm = m::mock('\Zend\Mvc\Controller\PluginManager');
        $pm->shouldReceive('get')->with('params')->andReturn($params);
        $pm->shouldReceive('setController')->with($sut);

        $request = m::mock('\Zend\Http\Request');
        $request->shouldReceive('isPost')->andReturn(false);
        $sut->getEvent()->setRequest($request);

        $service = m::mock('stdClass');
        $service->shouldReceive('getList')->with($paramsArr)->andReturn($data);

        $url = m::mock('Common\Service\Helper\UrlHelperService');
        $paramsArr['url'] = $url;

        $table = m::mock('Common\Service\Table\TableBuilder');
        $table->shouldReceive('buildTable')->with('users', $data, $paramsArr, false)->andReturn($table);

        $script = m::mock('stdClass');
        $script->shouldReceive('loadFiles')->once()->with(['lva-crud'])->andReturnNull();

        $sl = m::mock('\Zend\ServiceManager\ServiceManager');
        $sl->shouldReceive('get')->with('Entity\User')->andReturn($service);
        $sl->shouldReceive('get')->with('Helper\Url')->andReturn($url);
        $sl->shouldReceive('get')->with('Table')->andReturn($table);
        $sl->shouldReceive('get')->with('Script')->andReturn($script);

        $sut->setPluginManager($pm);
        $sut->setServiceLocator($sl);

        $view = $sut->indexAction();

        $this->assertInstanceOf('Olcs\View\Model\User', $view);
        $this->assertEquals($table, $view->getVariable('users'));
    }

    public function testSaveExistingRecord()
    {
        $rawEditData = array(
            'id' => 3,
            'version' => 1,
            'loginId' => 'stevefox',
            'memorableWord' => null,
            'emailAddress' => 'stevefox@test9876.com',

            'contactDetails' => array(
                'familyName' => 'Fox',
                'forename' => 'Steve',
                'writtenPermissionToEngage' => 'N',
                'emailAddress' => 'steve@example.com',
                'id' => 106,
                'version' => 1,

                'person' => array(
                    'birthPlace' => 'Zurich',
                    'otherName' => null,
                    'birthDate' => '1975-04-15',
                    'familyName' => 'Fox',
                    'forename' => 'Steve',
                    'id' => 82,
                    'version' => 1,
                ),

                'contactType' => array(
                    'id' => 'ct_team_user'
                )
            )
        );

        $id = 3;

        $controller = new UserController();

        $sl = m::mock('\Zend\ServiceManager\ServiceManager');
        $controller->setServiceLocator($sl);

        $br = new UserMappingContactDetails();
        $sl->shouldReceive('get')->with('BusinessRuleManager')->andReturnSelf();
        $sl->shouldReceive('get')->with('UserMappingContactDetails')->andReturn($br);

        $pm = m::mock('\Zend\Mvc\Controller\PluginManager');
        $pm->shouldReceive('setController')->with($controller);
        $controller->setPluginManager($pm);

        $request = m::mock('\Zend\Http\Request');
        $request->shouldReceive('isPost')->andReturn(false); // false NOT to simulate form submission
        $controller->getEvent()->setRequest($request);

        $params = m::mock('\Zend\Mvc\Controller\Plugin\Params');
        $params->shouldReceive('fromRoute')->with('id', null)->andReturn($id);
        $pm->shouldReceive('get')->with('params')->andReturn($params);

        $service = m::mock('stdClass');
        $service->shouldReceive('getUserDetails')->with($id)->andReturn($rawEditData);
        $sl->shouldReceive('get')->with('Entity\User')->andReturn($service);

        $flashMessenger = m::mock('stdClass');
        $flashMessenger->shouldReceive('addSuccessMessage')->andReturnNull(); // we don't care about this for this test.
        $sl->shouldReceive('get')->with('Helper\FlashMessenger')->andReturn($flashMessenger);

        $form = m::mock('Common\Form\Form');
        $form->shouldReceive('createFormWithRequest')->with('User', $request)->andReturnSelf();
        $form->shouldReceive('isValid')->andReturn(true); // irrelevant in this test
        $form->shouldReceive('setData')->with($controller->formatLoadData($rawEditData)); // happy path.
        $sl->shouldReceive('get')->with('Helper\Form')->andReturn($form);

        $view = $controller->editAction();

        $form = $view->getVariable('form');

        $this->assertInstanceOf('Common\Form\Form', $form);
    }

    public function testSaveWithPostData()
    {
        $rawEditData = array (
            'main' => array (
                'loginId' => 'stevefox',
                'forename' => 'Steve',
                'familyName' => 'Fox',
                'birthDate' => array (
                    'day' => '15',
                    'month' => '04',
                    'year' => '1975',
                ),
                'emailAddress' => 'steve@example.com',
                'emailConfirm' => 'steve@example.com',
                'memorableWord' => 'one',
                'id' => '3',
                'version' => '1',
            ),
            'contactDetailsId' => '106',
            'contactDetailsVersion' => '1',
            'personId' => '82',
            'personVersion' => '1',
            'contactType' => 'ct_team_user',
        );

        $controller = new UserController();

        $sl = m::mock('\Zend\ServiceManager\ServiceManager');
        $controller->setServiceLocator($sl);

        $br = new UserMappingContactDetails();
        $sl->shouldReceive('get')->with('BusinessRuleManager')->andReturnSelf();
        $sl->shouldReceive('get')->with('UserMappingContactDetails')->andReturn($br);

        $pm = m::mock('\Zend\Mvc\Controller\PluginManager');
        $pm->shouldReceive('setController')->with($controller);
        $controller->setPluginManager($pm);

        $service = m::mock('stdClass');
        $service->shouldReceive('save')->with($controller->formatSaveData($rawEditData));
        $sl->shouldReceive('get')->with('Entity\User')->andReturn($service);

        $request = m::mock('\Zend\Http\Request');
        $request->shouldReceive('isPost')->andReturn(true); // true to simulate form submission
        $controller->getEvent()->setRequest($request);

        $params = m::mock('\Zend\Mvc\Controller\Plugin\Params');
        $params->shouldReceive('fromPost')->withNoArgs()->andReturn($rawEditData);
        $params->shouldReceive('fromRoute')->withAnyArgs()->andReturnNull(); // not relevant but must be specified.
        $pm->shouldReceive('get')->with('params')->andReturn($params);

        $redirect = m::mock('Zend\Mvc\Controller\Plugin\Redirect');
        $redirect->shouldReceive('toRouteAjax')->with('user', ['action' => 'index'], [], false)->andReturn('redirect');
        $pm->shouldReceive('get')->with('redirect', null)->andReturn($redirect);

        $flashMessenger = m::mock('stdClass');
        $flashMessenger->shouldReceive('addSuccessMessage')->andReturnNull(); // we don't care about this for this test.
        $sl->shouldReceive('get')->with('Helper\FlashMessenger')->andReturn($flashMessenger);

        $form = m::mock('Common\Form\Form');
        $form->shouldReceive('createFormWithRequest')->with('User', $request)->andReturnSelf();
        $form->shouldReceive('isValid')->andReturn(true);
        $form->shouldReceive('setData')->with($rawEditData);
        $form->shouldReceive('getData')->andReturn($rawEditData);
        $sl->shouldReceive('get')->with('Helper\Form')->andReturn($form);

        $return = $controller->editAction();

        $this->assertEquals('redirect', $return);
    }
}
