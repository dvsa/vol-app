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
use Common\Service\Entity\TmEmploymentEntityService;
use Zend\View\Model\ViewModel;
use Common\Service\Data\CategoryDataService;
use Common\Service\Entity\ContactDetailsEntityService;

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
    public function setUpAction()
    {
        $this->sut = m::mock('\Olcs\Controller\TransportManager\Details\TransportManagerDetailsEmploymentController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test index action
     * 
     * @group tmEmployment
     */
    public function testIndexAction()
    {
        $this->setUpAction();

        $mockTmEmployment = m::mock()
            ->shouldReceive('getAllEmploymentsForTm')
            ->with(1)
            ->andReturn('results')
            ->getMock();

        $this->sm->setService('Entity\TmEmployment', $mockTmEmployment);

        $mockTable = m::mock()
            ->shouldReceive('render')
            ->andReturn('table')
            ->getMock();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-competence')
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
            ->with('tm.employments', 'results')
            ->andReturn($mockTable)
            ->shouldReceive('getViewWithTm')
            ->with(['table' => 'table'])
            ->andReturn($mockView)
            ->shouldReceive('renderView')
            ->with($mockView)
            ->andReturn(new ViewModel());

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test getDeleteServiceName
     * 
     * @group tmEmployment
     */
    public function testGetDeleteServiceName()
    {
        $this->setUpAction();
        $this->assertEquals('TmEmployment', $this->sut->getDeleteServiceName());
    }

    /**
     * Test index action with post
     * 
     * @group tmEmployment
     */
    public function testIndexActionWithPost()
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
            ->andReturn(new \Zend\Http\Response());

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with post and no crud action
     * 
     * @group tmEmployment
     */
    public function testIndexActionWithPostAndNoCrudAction()
    {
        $this->setUpAction();

        $mockView = m::mock()
            ->shouldReceive('setTemplate')
            ->with('pages/transport-manager/tm-competence')
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
            ->shouldReceive('getCrudActionFromPost')
            ->andReturn(false)
            ->shouldReceive('loadScripts')
            ->with(['table-actions'])
            ->shouldReceive('getEmploymentTable')
            ->andReturn(
                m::mock()
                ->shouldReceive('render')
                ->andReturn('table')
                ->getMock()
            )
            ->shouldReceive('getViewWithTm')
            ->with(['table' => 'table'])
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
    public function testAddAction()
    {
        $this->setUpAction();

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

        $response = $this->sut->addAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test edit action
     * 
     * @group tmEmployment
     */
    public function testEditAction()
    {
        $this->setUpAction();
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

        $this->sm->setService('Entity\TmEmployment', $mockTmEmployment);

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

        $response = $this->sut->editAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test add action with post
     * 
     * @group tmEmployment
     */
    public function testAddActionWitPost()
    {
        $this->setUpAction();

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

        $data = [
            'id' => 1,
            'version' => 1,
            'position' => 'pos',
            'hoursPerWeek' => 10,
            'contactDetails' => 1,
            'transportManager' => 1,
            'employerName' => 'name'
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

        $mockAddress = m::mock()
            ->shouldReceive('save')
            ->with(['address' => 'address'])
            ->andReturn(['id' => 1])
            ->getMock();

        $this->sm->setService('Entity\Address', $mockAddress);

        $mockContactDetails = m::mock()
            ->shouldReceive('save')
            ->with(['address' => 1, 'contactType' => ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_MANAGER])
            ->andReturn(['id' => 1])
            ->getMock();

        $this->sm->setService('Entity\ContactDetails', $mockContactDetails);

        $mockTmEmployment = m::mock()
            ->shouldReceive('save')
            ->with($data)
            ->getMock();

        $this->sm->setService('Entity\TmEmployment', $mockTmEmployment);

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

        $response = $this->sut->addAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test add action with post and cancel pressed
     * 
     * @group tmEmployment
     */
    public function testAddActionWitPostAndCancelPressed()
    {
        $this->setUpAction();

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

        $response = $this->sut->addAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }
}
