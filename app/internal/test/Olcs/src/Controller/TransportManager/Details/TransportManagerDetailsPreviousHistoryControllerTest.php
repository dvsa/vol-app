<?php

/**
 * Transport manager details previous history controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\TransportManager\Details;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Zend\View\Model\ViewModel;

/**
 * Transport manager details previous history controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsPreviousHistoryControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    protected $sut;

    /**
     * Set up action
     */
    public function setUp()
    {
        $this->markTestSkipped();
        $controllerClass = '\Olcs\Controller\TransportManager\Details\TransportManagerDetailsPreviousHistoryController';
        $this->sut = m::mock($controllerClass)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test index action
     *
     * @group tmPreviousHistory
     */
    public function testIndexAction()
    {
        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/form')
            ->shouldReceive('setTerminal')
            ->with(false)
            ->getMock();

        $mockFieldset = m::mock();

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('previousHistory')
            ->andReturn($mockFieldset)
            ->getMock();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler', 'tm-previous-history'])
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getViewWithTm')
            ->with(['form' => $mockForm])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $mockPreviousConvictionService = m::mock()
            ->shouldReceive('getDataForTransportManager')
            ->with(1)
            ->andReturn('RESULTS')
            ->getMock();

        $mockOtherLicenceService = m::mock()
            ->shouldReceive('getDataForTransportManager')
            ->with(1)
            ->andReturn('RESULTS')
            ->getMock();

        $mockFormHelper = m::mock()
            ->shouldReceive('createForm')
            ->with('TmPreviousHistory')
            ->andReturn($mockForm)
            ->getMock();

        $mockTmHelper = m::mock()
            ->shouldReceive('alterPreviousHistoryFieldset')
            ->with($mockFieldset, 1)
            ->getMock();

        $this->sm->setService('Entity\PreviousConviction', $mockPreviousConvictionService);
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicenceService);
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->sut->indexAction());
    }

    /**
     * Test index action
     *
     * @group tmPreviousHistory
     */
    public function testIndexActionPostCrudAction()
    {
        $postData = [
            'convictions' => 'foo'
        ];

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($postData)
                ->getMock()
            )
            ->shouldReceive('getCrudAction')
            ->with(['foo'])
            ->andReturn('CRUD')
            ->shouldReceive('handleCrudAction')
            ->with('CRUD', ['add-previous-conviction', 'add-previous-licence'], 'id')
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->indexAction());
    }

    /**
     * Test index action
     *
     * @group tmPreviousHistory
     */
    public function testIndexActionPostCrudActionPreviousLicence()
    {
        $postData = [
            'previousLicences' => 'foo'
        ];

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($postData)
                ->getMock()
            )
            ->shouldReceive('getCrudAction')
            ->with(['foo'])
            ->andReturn('CRUD')
            ->shouldReceive('handleCrudAction')
            ->with('CRUD', ['add-previous-conviction', 'add-previous-licence'], 'id')
            ->andReturn('RESPONSE');

        $this->assertEquals('RESPONSE', $this->sut->indexAction());
    }

    /**
     * Test index action with post and no action
     *
     * @group tmPreviousHistory
     */
    public function testIndexActionWithPostNoAction()
    {
        $postData = [

        ];

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/form')
            ->shouldReceive('setTerminal')
            ->with(false)
            ->getMock();

        $mockFieldset = m::mock();

        $mockForm = m::mock()
            ->shouldReceive('get')
            ->with('previousHistory')
            ->andReturn($mockFieldset)
            ->getMock();

        $this->sut->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->shouldReceive('getPost')
                ->andReturn($postData)
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler', 'tm-previous-history'])
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getViewWithTm')
            ->with(['form' => $mockForm])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $mockFormHelper = m::mock()
            ->shouldReceive('createForm')
            ->with('TmPreviousHistory')
            ->andReturn($mockForm)
            ->getMock();

        $mockTmHelper = m::mock()
            ->shouldReceive('alterPreviousHistoryFieldset')
            ->with($mockFieldset, 1)
            ->getMock();

        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->sut->indexAction());
    }

    /**
     * Test delete previous conviction action
     *
     * @group tmPreviousHistory
     */
    public function testDeletePreviousConvictionAction()
    {
        $this->markTestSkipped();

        $this->sut->shouldReceive('deleteRecords')
            ->with('Entity\PreviousConviction')
            ->andReturn('mixed');

        $this->assertEquals('mixed', $this->sut->deletePreviousConvictionAction());
    }

    /**
     * Test delete previous licence action
     *
     * @group tmPreviousHistory
     */
    public function testDeletePreviousLicenceAction()
    {
        $this->markTestSkipped();

        $this->sut->shouldReceive('deleteRecords')
            ->with('Entity\OtherLicence')
            ->andReturn('mixed');

        $this->assertEquals('mixed', $this->sut->deletePreviousLicenceAction());
    }

    /**
     * Test edit previous conviction action
     *
     * @dataProvider editActionsProvider
     * @group tmPreviousHistory
     */
    public function testEditPreviousConvictionAction($serviceName, $formName, $fieldsetName, $actionName)
    {
        $this->markTestSkipped();

        $mockPreviousConvictionService = m::mock()
            ->shouldReceive('getById')
            ->with(1)
            ->andReturn('data')
            ->getMock();

        $this->sm->setService($serviceName, $mockPreviousConvictionService);

        $mockForm = m::mock()
            ->shouldReceive('setData')
            ->with([$fieldsetName => 'data'])
            ->getMock();

        $this->sut
            ->shouldReceive('getForm')
            ->with($formName)
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('isButtonPressed')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('formPost')
            ->with($mockForm, 'processForm')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('renderView')
            ->andReturn('view');

        $this->sm->setService(
            'Helper\Form',
            m::mock()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother')
            ->getMock()
        );

        $this->assertEquals('view', $this->sut->$actionName());
    }

    /**
     * Test edit previous conviction action with cancel
     *
     * @group tmPreviousHistory
     */
    public function testEditPreviousConvictionActionWithCancel()
    {
        $this->sut
            ->shouldReceive('getForm')
            ->with('tm-convictions-and-penalties')
            ->andReturn('form')
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->editPreviousConvictionAction());
    }

    /**
     * Test edit previous history action with post
     *
     * @dataProvider editActionsProvider
     * @group tmPreviousHistory
     */
    public function testEditPreviousHistoryActionWithPost($serviceName, $formName, $fieldsetName, $actionName)
    {
        $this->markTestSkipped();

        $post = [
            $fieldsetName => ['details' => 'details']
        ];

        $data = [
            'details' => 'details',
            'transportManager' => 1
        ];

        $mockService = m::mock()
            ->shouldReceive('save')
            ->with($data)
            ->getMock();

        $this->sm->setService($serviceName, $mockService);

        $mockForm = m::mock()
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $this->sut
            ->shouldReceive('getForm')
            ->with($formName)
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect')
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('redirect')
                ->getMock()
            );

        $this->sm->setService(
            'Helper\Form',
            m::mock()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother')
            ->getMock()
        );

        $this->assertInstanceOf('Zend\Http\Response', $this->sut->$actionName());
    }

    /**
     * Test previous conviction add action
     *
     * @group tmPreviousHistory
     */
    public function testAddPreviousConvictionAction()
    {
        $this->sut->shouldReceive('formAction')
            ->with('Add', 'TmConvictionsAndPenalties')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->addPreviousConvictionAction());
    }

    /**
     * Test previous licence add action
     *
     * @group tmPreviousHistory
     */
    public function testAddPreviousLicenceAction()
    {
        $this->sut->shouldReceive('formAction')
            ->with('Add', 'TmPreviousLicences')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->addPreviousLicenceAction());
    }

    /**
     * Test previous conviction add another clicked
     *
     * @group tmPreviousHistory
     */
    public function testPreviousConvictionAddAnotherAction()
    {
        $this->markTestSkipped();

        $post = [
            'tm-convictions-and-penalties-details' => ['details' => 'details']
        ];

        $data = [
            'details' => 'details',
            'transportManager' => 1
        ];

        $mockService = m::mock()
            ->shouldReceive('save')
            ->with($data)
            ->getMock();

        $this->sm->setService('Entity\PreviousConviction', $mockService);

        $mockForm = m::mock()
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('setData')
            ->with($post)
            ->shouldReceive('isValid')
            ->andReturn(true)
            ->shouldReceive('getData')
            ->andReturn($post)
            ->getMock();

        $this->sut
            ->shouldReceive('getForm')
            ->with('TmConvictionsAndPenalties')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($post)
                ->getMock()
            )
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('isButtonPressed')
            ->with('addAnother')
            ->andReturn(true)
            ->shouldReceive('fromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('redirect')
            ->andReturn(
                m::mock('Zend\Http\Redirect')
                ->shouldReceive('toRoute')
                ->with(null, ['transportManager' => 1, 'action' => 'add-previous-conviction'])
                ->andReturnSelf()
                ->getMock()
            )
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('redirect')
                ->getMock()
            );

        $this->sm->setService(
            'Helper\Form',
            m::mock()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother')
            ->getMock()
        );

        $this->assertInstanceOf('Zend\Http\Response', $this->sut->editPreviousConvictionAction());
    }

    /**
     * Data provider
     */
    public function editActionsProvider()
    {
        return [
            [
                'Entity\PreviousConviction',
                'TmConvictionsAndPenalties',
                'tm-convictions-and-penalties-details',
                'editPreviousConvictionAction'
            ],
            [
                'Entity\OtherLicence',
                'TmPreviousLicences',
                'tm-previous-licences-details',
                'editPreviousLicenceAction'
            ],
        ];
    }
}
