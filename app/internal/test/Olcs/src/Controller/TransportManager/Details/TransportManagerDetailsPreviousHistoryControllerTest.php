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
use Common\Service\Entity\PreviousConvictionEntityService;
use Common\Service\Entity\OtherLicenceLicenceEntityService;
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

    /**
     * Set up action
     */
    public function setUpAction()
    {
        $this->sut =
            m::mock('\Olcs\Controller\TransportManager\Details\TransportManagerDetailsPreviousHistoryController')
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
        $this->setUpAction();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/multi-tables')
            ->shouldReceive('setTerminal')
            ->with(false)
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
            ->with(['table-actions'])
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getTable')
            ->with('tm.convictionsandpenalties', 'RESULTS')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('topTable')
                ->getMock()
            )
            ->shouldReceive('getTable')
            ->with('tm.previouslicences', 'RESULTS')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('bottomTable')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->with(['topTable' => 'topTable', 'bottomTable' => 'bottomTable'])
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

        $this->sm->setService('Entity\PreviousConviction', $mockPreviousConvictionService);
        $this->sm->setService('Entity\OtherLicence', $mockOtherLicenceService);

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->sut->indexAction());
    }

    /**
     * Test index action
     *
     * @group tmPreviousHistory
     */
    public function testIndexActionPost()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->getMock()
            )
            ->shouldReceive('checkForCrudAction')
            ->andReturn(new \Zend\Http\Response);

        $this->assertInstanceOf('\Zend\Http\Response', $this->sut->indexAction());
    }

    /**
     * Test index action with post and no action
     *
     * @group tmPreviousHistory
     */
    public function testIndexActionWithPostNoAction()
    {
        $this->setUpAction();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/multi-tables')
            ->shouldReceive('setTerminal')
            ->with(false)
            ->getMock();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('checkForCrudAction')
            ->andReturn(false)
            ->shouldReceive('loadScripts')
            ->with(['table-actions'])
            ->shouldReceive('getConvictionsAndPenaltiesTable')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('topTable')
                ->getMock()
            )
            ->shouldReceive('getPreviousLicencesTable')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('bottomTable')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->with(
                ['topTable' => 'topTable', 'bottomTable' => 'bottomTable']
            )
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $this->assertInstanceOf('Zend\View\Model\ViewModel', $this->sut->indexAction());
    }

    /**
     * Test delete previous conviction action
     *
     * @group tmPreviousHistory
     */
    public function testDeletePreviousConvictionAction()
    {
        $this->setUpAction();

        $mockView = m::mock('Zend\View\Model\ViewModel');

        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->with('internal.transport-manager.previous-history.delete-question')
            ->andReturn('translated message')
            ->getMock();

        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('confirm')
            ->with('translated message')
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn('rendered view');

        $this->assertEquals('rendered view', $this->sut->deletePreviousConvictionAction());
    }

    /**
     * Test delete previous conviction action with POST
     *
     * @group tmPreviousHistory
     */
    public function testDeletePreviousConvictionActionWitPost()
    {
        $this->setUpAction();

        $mockTranslator = m::mock()
            ->shouldReceive('translate')
            ->withAnyArgs()
            ->andReturn('translated message')
            ->getMock();

        $this->sm->setService('translator', $mockTranslator);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('confirm')
            ->with('translated message')
            ->andReturn('redirect')
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(false)
            ->shouldReceive('addSuccessMessage')
            ->with('translated message')
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $mockPreviousConvictionService = m::mock()
            ->shouldReceive('delete')
            ->with(1)
            ->getMock();

        $this->sm->setService('Entity\PreviousConviction', $mockPreviousConvictionService);

        $this->assertEquals('redirect', $this->sut->deletePreviousConvictionAction());
    }

    /**
     * Test delete previous licence action
     *
     * @group tmPreviousHistory
     */
    public function testDeletePreviousLicenceAction()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('deletePreviousHistoryRecord')
            ->with('Entity\OtherLicence')
            ->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->deletePreviousLicenceAction());
    }

    /**
     * Test edit previous conviction action
     *
     * @dataProvider editActionsProvider
     * @group tmPreviousHistory
     */
    public function testEditPreviousConvictionAction($serviceName, $formName, $fieldsetName, $actionName)
    {
        $this->setUpAction();

        $mockPreviousConvictionService = m::mock()
            ->shouldReceive('getById')
            ->with(1)
            ->andReturn('data')
            ->getMock();

        $this->sm->setService($serviceName, $mockPreviousConvictionService);

        $mockForm = m::mock()
            ->shouldReceive('getName')
            ->andReturn($formName)
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

        $this->assertEquals('view', $this->sut->$actionName());
    }

    /**
     * Test edit previous conviction action with cancel
     *
     * @group tmPreviousHistory
     */
    public function testEditPreviousConvictionActionWithCancel()
    {
        $this->setUpAction();

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
        $this->setUpAction();

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

        $this->assertInstanceOf('Zend\Http\Response', $this->sut->$actionName());
    }

    /**
     * Data provider
     */
    public function editActionsProvider()
    {
        return [
            [
                'Entity\PreviousConviction',
                'tm-convictions-and-penalties',
                'tm-convictions-and-penalties-details',
                'editPreviousConvictionAction'
            ],
            [
                'Entity\OtherLicence',
                'tm-previous-licences',
                'tm-previous-licences-details',
                'editPreviousLicenceAction'
            ],
        ];
    }

    /**
     * Test previous conviction add action
     *
     * @group tmPreviousHistory
     */
    public function testPreviousConvictionAddAction()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('formAction')
            ->with('Add', 'tm-convictions-and-penalties')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->previousConvictionAddAction());
    }

    /**
     * Test previous licence add action
     *
     * @group tmPreviousHistory
     */
    public function testPreviousLicenceAddAction()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('formAction')
            ->with('Add', 'tm-previous-licences')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->previousLicenceAddAction());
    }
}
