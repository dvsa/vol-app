<?php

/**
 * Transport manager details employment controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\TransportManager\Details;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Zend\View\Model\ViewModel;

/**
* Transport manager details employment controller tests
  *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsEmploymentControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    /**
     * Set up action
     */
    public function setUp()
    {
        $this->sut = m::mock('\Olcs\Controller\TransportManager\Details\TransportManagerDetailsEmploymentController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        //$this->sut->setEnabledCsrf(false);
    }

    /**
     * Test index action
     *
     * @group tmEmployment
     */
    public function testIndexAction()
    {
        $this->markTestSkipped();

        $mockTmEmployment = m::mock()
            ->shouldReceive('getAllEmploymentsForTm')
            ->with(1)
            ->andReturn('results')
            ->getMock();

        $mockForm = m::mock();
        $mockOtherEmployment = m::mock();
        $mockFormHelper = m::mock();
        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);
        $this->sm->setService('Entity\TmEmployment', $mockTmEmployment);

        $mockFormHelper->shouldReceive('createForm')
            ->with('TmOtherEmployment')
            ->andReturn($mockForm);

        $mockForm->shouldReceive('get')
            ->with('otherEmployment')
            ->andReturn($mockOtherEmployment);

        $mockTmHelper->shouldReceive('prepareOtherEmploymentTable')
            ->with($mockOtherEmployment, 1);

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/form')
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
            ->with(['forms/crud-table-handler', 'tm-other-employment'])
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getViewWithTm')
            ->with(['form' => $mockForm])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test index action with post
     *
     * @group tmEmployment
     */
    public function testIndexActionWithPostAndCrudAction()
    {
        $this->markTestSkipped();

        $postData = [
            'employment' => [
                'action' => 'add'
            ]
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
            ->shouldReceive('handleCrudAction')
            ->with(['action' => 'add'], ['add-employment'], 'id')
            ->andReturn('RESPONSE');

        $response = $this->sut->indexAction();
        $this->assertEquals('RESPONSE', $response);
    }

    /**
     * Test index action with post and no crud action
     *
     * @group tmEmployment
     */
    public function testIndexActionWithPostAndNoCrudAction()
    {
        $this->markTestSkipped();

        $postData = [];

        $mockTmEmployment = m::mock()
            ->shouldReceive('getAllEmploymentsForTm')
            ->with(1)
            ->andReturn('results')
            ->getMock();

        $mockForm = m::mock();
        $mockOtherEmployment = m::mock();
        $mockFormHelper = m::mock();
        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\Form', $mockFormHelper);
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);
        $this->sm->setService('Entity\TmEmployment', $mockTmEmployment);

        $mockFormHelper->shouldReceive('createForm')
            ->with('TmOtherEmployment')
            ->andReturn($mockForm);

        $mockForm->shouldReceive('get')
            ->with('otherEmployment')
            ->andReturn($mockOtherEmployment);

        $mockTmHelper->shouldReceive('prepareOtherEmploymentTable')
            ->with($mockOtherEmployment, 1);

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/form')
            ->shouldReceive('setTerminal')
            ->with(false)
            ->getMock();

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getPost')
                ->andReturn($postData)
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('loadScripts')
            ->with(['forms/crud-table-handler', 'tm-other-employment'])
            ->shouldReceive('params')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getViewWithTm')
            ->with(['form' => $mockForm])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test add action
     *
     * @group tmEmployment
     */
    public function testAddEmploymentAction()
    {
        $this->markTestSkipped();

        $mockForm = m::mock()
            ->shouldReceive('remove')
            ->with('csrf')
            ->getMock();

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('tm-employment')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('renderView')
            ->andReturn(new ViewModel());

        $response = $this->sut->addEmploymentAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test edit action
     *
     * @group tmEmployment
     */
    public function testEditEmploymentAction()
    {
        $this->markTestSkipped();

        $employmentData = [
            'id' => 1,
            'version' => 1,
            'position' => 'pos',
            'hoursPerWeek' => 10,
            'employerName' => 'name',
            'contactDetails' =>  ['address' => 'address']
        ];

        $data = [
            'tm-employment-details' => [
                'id' => 1,
                'version' => 1,
                'position' => 'pos',
                'hoursPerWeek' => 10
            ],
            'tm-employer-name-details' => [
                'employerName' => 'name'
            ],
            'address' => 'address'
        ];

        $mockTmEmployment = m::mock()
            ->shouldReceive('getEmployment')
            ->with(1)
            ->andReturn($employmentData)
            ->getMock();

        $mockTmHelper = m::mock();
        $this->sm->setService('Helper\TransportManager', $mockTmHelper);
        $this->sm->setService('Entity\TmEmployment', $mockTmEmployment);

        $mockTmHelper->shouldReceive('getOtherEmploymentData')
            ->with(1)
            ->andReturn($data);

        $mockForm = m::mock()
            ->shouldReceive('remove')
            ->with('csrf')
            ->shouldReceive('setData')
            ->with($data)
            ->getMock();

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('tm-employment')
            ->andReturn($mockForm)
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->getMock()
            )
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock()
                ->shouldReceive('getContent')
                ->andReturn('')
                ->getMock()
            )
            ->shouldReceive('renderView')
            ->andReturn(new ViewModel());

        $this->sm->setService(
            'Helper\Form',
            m::mock()
            ->shouldReceive('remove')
            ->with($mockForm, 'form-actions->addAnother')
            ->getMock()
        );

        $response = $this->sut->editEmploymentAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test add action with post
     *
     * @group tmEmployment
     */
    public function testAddEmploymentActionWithPost()
    {
        $this->markTestSkipped();

        $post = [
            'tm-employment-details' => [
                'id' => 1,
                'version' => 1,
                'position' => 'pos',
                'hoursPerWeek' => 10
            ],
            'tm-employer-name-details' => [
                'employerName' => 'name'
            ],
            'address' => [
                'address' => 'address'
            ]
        ];

        $expectedParams = [
            'address' => [
                'address' => 'address'
            ],
            'data' => [
                'employerName' => 'name',
                'id' => 1,
                'version' => 1,
                'position' => 'pos',
                'hoursPerWeek' => 10,
                'transportManager' => 1
            ]
        ];

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

        $mockTmEmploymentBs = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService('TmEmployment', $mockTmEmploymentBs);

        $this->sm->setService('BusinessServiceManager', $bsm);

        $mockTmEmploymentBs->shouldReceive('process')
            ->once()
            ->with($expectedParams);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('tm-employment')
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
            ->andReturn(new \Zend\Http\Response())
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('response')
                ->getMock()
            );

        $response = $this->sut->addEmploymentAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test add action with post and cancel pressed
     *
     * @group tmEmployment
     */
    public function testAddEmploymentActionWitPostAndCancelPressed()
    {
        $this->markTestSkipped();

        $post = [
            'tm-employment-details' => [
                'id' => 1,
                'version' => 1,
                'position' => 'pos',
                'hoursPerWeek' => 10
            ],
            'tm-employer-name-details' => [
                'employerName' => 'name'
            ],
            'address' => [
                'id' => 1
            ]
        ];

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
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('tm-employment')
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
            ->andReturn(true)
            ->shouldReceive('redirectToIndex')
            ->andReturn(true)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('response')
                ->getMock()
            );

        $response = $this->sut->addEmploymentAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test delete action
     *
     * @group tmEmployment
     */
    public function testDeleteEmploymentAction()
    {
        $this->markTestSkipped();

        $this->sm->setService(
            'translator',
            m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.previous-history.delete-question')
            ->andReturn('message')
            ->getMock()
        );

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn('')
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromQuery')
                ->with('id')
                ->andReturn([1, 2])
                ->getMock()
            )
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn(new ViewModel())
            ->shouldReceive('renderView')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->deleteEmploymentAction());
    }

    /**
     * Test delete action
     *
     * @group tmEmployment
     */
    public function testDeleteEmploymentActionMultipleRoute()
    {
        $this->markTestSkipped();

        $this->sm->setService(
            'translator',
            m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.previous-history.delete-question')
            ->andReturn('message')
            ->getMock()
        );

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn('1,2')
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn(new ViewModel())
            ->shouldReceive('renderView')
            ->andReturn('view');

        $this->assertEquals('view', $this->sut->deleteEmploymentAction());
    }

    /**
     * Test delete action with post
     *
     * @group tmEmployment
     */
    public function testDeleteEmploymentActionWithPost()
    {
        $this->markTestSkipped();

        $this->sm->setService(
            'translator',
            m::mock()
            ->shouldReceive('translate')
            ->with('transport-manager.previous-history.delete-question')
            ->andReturn('message')
            ->getMock()
        );

        $this->sm->setService(
            'Entity\TmEmployment',
            m::mock()
            ->shouldReceive('deleteListByIds')
            ->with(['id' => [1, 2]])
            ->getMock()
        );

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn('')
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromQuery')
                ->with('id')
                ->andReturn([1, 2])
                ->getMock()
            )
            ->shouldReceive('confirm')
            ->with('message')
            ->andReturn('redirect')
            ->shouldReceive('addSuccessMessage')
            ->with('transport-manager.deleted-message')
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->deleteEmploymentAction());
    }

    /**
     * Test delete action with post
     *
     * @group tmEmployment
     */
    public function testDeleteEmploymentActionWithCancel()
    {
        $this->markTestSkipped();

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->with('cancel')
            ->andReturn(true)
            ->shouldReceive('redirectToIndex')
            ->andReturn('redirect');

        $this->assertEquals('redirect', $this->sut->deleteEmploymentAction());
    }

    /**
     * Test add another action with post
     *
     * @group tmEmployment
     */
    public function testAddAnotherActionWithPost()
    {
        $this->markTestSkipped();

        $post = [
            'tm-employment-details' => [
                'id' => 1,
                'version' => 1,
                'position' => 'pos',
                'hoursPerWeek' => 10
            ],
            'tm-employer-name-details' => [
                'employerName' => 'name'
            ],
            'address' => [
                'address' => 'address'
            ]
        ];

        $expectedParams = [
            'address' => [
                'address' => 'address'
            ],
            'data' => [
                'employerName' => 'name',
                'id' => 1,
                'version' => 1,
                'position' => 'pos',
                'hoursPerWeek' => 10,
                'transportManager' => 1
            ]
        ];

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

        $mockTmEmploymentBs = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $bsm->setService('TmEmployment', $mockTmEmploymentBs);

        $this->sm->setService('BusinessServiceManager', $bsm);

        $mockTmEmploymentBs->shouldReceive('process')
            ->once()
            ->with($expectedParams);

        $this->sut
            ->shouldReceive('getFromRoute')
            ->with('id')
            ->andReturn(1)
            ->shouldReceive('getForm')
            ->with('tm-employment')
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
            ->shouldReceive('isButtonPressed')
            ->with('addAnother')
            ->andReturn(true)
            ->shouldReceive('getFromRoute')
            ->with('transportManager')
            ->andReturn(1)
            ->shouldReceive('getResponse')
            ->andReturn(
                m::mock('Zend\Http\Response')
                ->shouldReceive('getContent')
                ->andReturn('response')
                ->getMock()
            );

        $params = [
            'transportManager' => 1,
            'action' => 'add-employment'
        ];

        $this->sut->shouldReceive('redirect->toRoute')
            ->with(null, $params);

        $response = $this->sut->addEmploymentAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }
}
