<?php

/**
 * Transport manager details detail controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
namespace OlcsTest\Controller\TransportManager\Details;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;
use OlcsTest\Bootstrap;
use Mockery as m;
use Common\Service\Entity\TransportManagerEntityService;

/**
 * Transport manager details detail controller tests
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class TransportManagerDetailsDetailControllerTest extends AbstractHttpControllerTestCase
{
    /**
     * @var ServiceManager
     */
    protected $sm;

    /**
     * @var array
     */
    protected $post = [
        'transport-manager-details' => [
            'id' => 1,
            'version' => 1,
            'title' => 'Mr',
            'firstName' => 'Tom',
            'lastName' => 'Jones',
            'emailAddress' => 'some@email.com',
            'birthDate' => [
                'day' => '01',
                'month' => '03',
                'year' => '1972'
            ],
            'birthPlace' => 'Leeds',
            'type' => 'tm_t_B',
            'status' => 'tm_st_A',
            'contactDetailsId' => 104,
            'contactDetailsVersion' => 4,
            'personId' => 77,
            'personVersion' => 15
        ],
        'home-address' => [
            'id' => 104,
            'version' => 3,
            'addressLine1' => 'Unit 9',
            'addressLine2' => 'Shapely Industrial Estate',
            'addressLine3' => 'Harehills',
            'addressLine4' => '',
            'town' => 'Leeds',
            'postcode' => 'LS9 2FA'
        ],
        'form-actions' => [
            'save'
        ],
        'js-submit' => 1
    ];

    protected $tmDetails = [
        'version' => 1,
        'contactDetails' => [
            'id' => 1,
            'version' => 1,
            'emailAddress' => 'email@address.com',
            'person' => [
                'id' => 1,
                'version' => 1,
                'forename' => 'First',
                'familyName' => 'Last',
                'title' => 'Mr',
                'birthDate' => '1973-01-01',
                'birthPlace' => 'London'
            ],
            'address' => [
                'id' => 1,
                'version' => 1,
                'addressLine1' => 'addressLine1',
                'addressLine2' => 'addressLine2',
                'addressLine3' => 'addressLine3',
                'addressLine4' => 'addressLine4',
                'town' => 'Town',
                'postcode' => 'PC'
            ],
            'contactType' => [
                'id' => 'ct_tm'
            ]
        ],
        'tmType' => [
            'id' => 'tm_t_B'
        ],
        'tmStatus' => [
            'id' => 'tm_st_A'
        ]
    ];

    /**
     * Set up action
     */
    public function setUpAction()
    {
        $this->sut = m::mock('\Olcs\Controller\TransportManager\Details\TransportManagerDetailsDetailController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);
    }

    /**
     * Test index action with edit transport manager
     * 
     * @group transportManagerDetails
     */
    public function testIndexActionWithEditTransportManager()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('transportManager')
                ->andReturn(1)
                ->getMock()
            );

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->andReturn(false);

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->shouldReceive('getUri')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getPath')
                    ->andReturn('/')
                    ->getMock()
                )
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            );

        $mockTmDetails = m::mock()
            ->shouldReceive('getTmDetails')
            ->with(1)
            ->andReturn($this->tmDetails)
            ->getMock();

        $this->sm->setService('Entity\TransportManager', $mockTmDetails);

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test index action with post edit transport manager
     *
     * @group transportManagerDetails
     */
    public function testIndexActionWithPostEditTransportManager()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('transportManager')
                ->andReturn(1)
                ->getMock()
            );

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->andReturn(false);

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getUri')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getPath')
                    ->andReturn('/')
                    ->getMock()
                )
                ->shouldReceive('getPost')
                ->andReturn($this->post)
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            );

        $mockTmDetails = m::mock()
            ->shouldReceive('getTmDetails')
            ->andReturn($this->tmDetails)
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['id'],
                    'version' => $this->post['transport-manager-details']['version'],
                    'tmType' => $this->post['transport-manager-details']['type'],
                    'tmStatus' => $this->post['transport-manager-details']['status'],
                    'contactDetails' => 1,
                    'modifiedBy' => ''
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $mockRefDataService = m::mock('Common\Service\Data\RefData');
        $mockRefDataService->shouldReceive('fetchListOptions')
            ->with('tm_type', false)
            ->andReturn(
                ['tm_t_B' => 'Both', 'tm_t_E' => 'External', 'tm_t_I' => 'Internal']
            );

        $mockAddressService = m::mock()
            ->shouldReceive('save')
            ->with($this->post['home-address'])
            ->andReturn(['id' => 1])
            ->getMock();

        $mockPersonService = m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['personId'],
                    'version' => $this->post['transport-manager-details']['personVersion'],
                    'title' => $this->post['transport-manager-details']['title'],
                    'forename' => $this->post['transport-manager-details']['firstName'],
                    'familyName' => $this->post['transport-manager-details']['lastName'],
                    'birthDate' =>
                        $this->post['transport-manager-details']['birthDate']['year'] . '-' .
                        $this->post['transport-manager-details']['birthDate']['month'] . '-' .
                        $this->post['transport-manager-details']['birthDate']['day'],
                    'birthPlace' => $this->post['transport-manager-details']['birthPlace']
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $mockContactDetailsService = m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['contactDetailsId'],
                    'version' => $this->post['transport-manager-details']['contactDetailsVersion'],
                    'person' => 1,
                    'address' => 1,
                    'emailAddress' => $this->post['transport-manager-details']['emailAddress'],
                    'contactType' => 'ct_tm'
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $this->sm->setService('Common\Service\Data\RefData', $mockRefDataService);
        $this->sm->setService('Entity\TransportManager', $mockTmDetails);
        $this->sm->setService('Entity\Address', $mockAddressService);
        $this->sm->setService('Entity\Person', $mockPersonService);
        $this->sm->setService('Entity\ContactDetails', $mockContactDetailsService);

        $this->sut
            ->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                ->shouldReceive('addSuccessMessage')
                ->with('The transport manager has been updated successfully')
                ->getMock()
            );

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test index action with edit transport manager and cancel button pressed
     *
     * @group transportManagerDetails
     */
    public function testIndexActionWithEditTransportManagerAndCancelButtonPressed()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('transportManager')
                ->andReturn(1)
                ->getMock()
            );

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->andReturn(true);

        $this->sut
            ->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                ->shouldReceive('addSuccessMessage')
                ->with('Your changes have been discarded')
                ->getMock()
            );

        $this->sut
            ->shouldReceive('redirectToRoute')
            ->with('transport-manager/details', ['transportManager' => 1])
            ->andReturn(new \Zend\Http\Response());

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with add transport manager and cancel button pressed
     *
     * @group transportManagerDetails
     */
    public function testIndexActionWithAddTransportManagerAndCancelButtonPressed()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('transportManager')
                ->andReturn(false)
                ->getMock()
            );

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->andReturn(true);

        $this->sut
            ->shouldReceive('redirectToRoute')
            ->with('operators/operators-params')
            ->andReturn(new \Zend\Http\Response());

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }

    /**
     * Test index action with add transport manager
     * 
     * @group transportManagerDetails
     */
    public function testIndexActionWithAddTransportManager()
    {
        $this->setUpAction();

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('transportManager')
                ->andReturn(false)
                ->getMock()
            );

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->andReturn(false);

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(false)
                ->shouldReceive('getUri')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getPath')
                    ->andReturn('/')
                    ->getMock()
                )
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            );

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\View\Model\ViewModel', $response);
    }

    /**
     * Test index action with post add transport manager
     *
     * @group transportManagerDetails
     */
    public function testIndexActionWithPostAddTransportManager()
    {
        $this->setUpAction();
        $this->post['transport-manager-details']['id'] = '';

        $this->sut
            ->shouldReceive('params')
            ->andReturn(
                m::mock()
                ->shouldReceive('fromRoute')
                ->with('transportManager')
                ->andReturn(1)
                ->getMock()
            );

        $this->sut
            ->shouldReceive('isButtonPressed')
            ->andReturn(false);

        $this->sut
            ->shouldReceive('getRequest')
            ->andReturn(
                m::mock()
                ->shouldReceive('isPost')
                ->andReturn(true)
                ->shouldReceive('getUri')
                ->andReturn(
                    m::mock()
                    ->shouldReceive('getPath')
                    ->andReturn('/')
                    ->getMock()
                )
                ->shouldReceive('getPost')
                ->andReturn($this->post)
                ->shouldReceive('isXmlHttpRequest')
                ->andReturn(false)
                ->getMock()
            );

        $mockTmDetails = m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['id'],
                    'version' => $this->post['transport-manager-details']['version'],
                    'tmType' => $this->post['transport-manager-details']['type'],
                    'tmStatus' => $this->post['transport-manager-details']['status'],
                    'contactDetails' => 1,
                    'createdBy' => ''
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $mockRefDataService = m::mock('Common\Service\Data\RefData');
        $mockRefDataService->shouldReceive('fetchListOptions')
            ->with('tm_type', false)
            ->andReturn(
                ['tm_t_B' => 'Both', 'tm_t_E' => 'External', 'tm_t_I' => 'Internal']
            );

        $mockAddressService = m::mock()
            ->shouldReceive('save')
            ->with($this->post['home-address'])
            ->andReturn(['id' => 1])
            ->getMock();

        $mockPersonService = m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['personId'],
                    'version' => $this->post['transport-manager-details']['personVersion'],
                    'title' => $this->post['transport-manager-details']['title'],
                    'forename' => $this->post['transport-manager-details']['firstName'],
                    'familyName' => $this->post['transport-manager-details']['lastName'],
                    'birthDate' =>
                        $this->post['transport-manager-details']['birthDate']['year'] . '-' .
                        $this->post['transport-manager-details']['birthDate']['month'] . '-' .
                        $this->post['transport-manager-details']['birthDate']['day'],
                    'birthPlace' => $this->post['transport-manager-details']['birthPlace']
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $mockContactDetailsService = m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['contactDetailsId'],
                    'version' => $this->post['transport-manager-details']['contactDetailsVersion'],
                    'person' => 1,
                    'address' => 1,
                    'emailAddress' => $this->post['transport-manager-details']['emailAddress'],
                    'contactType' => 'ct_tm'
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $this->sm->setService('Common\Service\Data\RefData', $mockRefDataService);
        $this->sm->setService('Entity\TransportManager', $mockTmDetails);
        $this->sm->setService('Entity\Address', $mockAddressService);
        $this->sm->setService('Entity\Person', $mockPersonService);
        $this->sm->setService('Entity\ContactDetails', $mockContactDetailsService);

        $this->sut
            ->shouldReceive('flashMessenger')
            ->andReturn(
                m::mock()
                ->shouldReceive('addSuccessMessage')
                ->with('The Transport Manager has been created successfully')
                ->getMock()
            );

        $this->sut
            ->shouldReceive('redirectToRoute')
            ->with('transport-manager/details/details', ['transportManager' => 1])
            ->andReturn(new \Zend\Http\Response());

        $responseMock = m::mock('\Zend\Http\Response')
            ->shouldReceive('getStatusCode')
            ->andReturn(302)
            ->getMock();

        $this->sut
            ->shouldReceive('getResponse')
            ->andReturn($responseMock);

        $response = $this->sut->indexAction();
        $this->assertInstanceOf('Zend\Http\Response', $response);
    }
}
