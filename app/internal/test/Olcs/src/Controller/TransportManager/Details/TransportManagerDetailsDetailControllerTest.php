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

    protected $post = [
        'transport-manager-details' => [
            'id' => 1,
            'version' => 1,
            'type' => 'tm_t_B',
            'status' => 'tm_st_A',
            'homeCdId' => 1,
            'homeCdVersion' => 1,
            'workCdId' => 2,
            'workCdVersion' => 2,
            'emailAddress' => 'email@address.com',
            'personId' => 1,
            'personVersion' => 1,
            'title' => 'Mr',
            'firstName' => 'First',
            'lastName' => 'Last',
            'birthPlace' => 'London',
            'birthDate' => '1973-01-01',
        ],
        'home-address' => [
            'id' => 1,
            'version' => 1,
            'addressLine1' => 'addressLine1',
            'addressLine2' => 'addressLine2',
            'addressLine3' => 'addressLine3',
            'addressLine4' => 'addressLine4',
            'town' => 'Town',
            'postcode' => 'PC'
        ],
        'work-address' => [
            'id' => 2,
            'version' => 2,
            'addressLine1' => 'addressLine21',
            'addressLine2' => 'addressLine22',
            'addressLine3' => 'addressLine23',
            'addressLine4' => 'addressLine24',
            'town' => 'Town',
            'postcode' => 'PC'
        ]
    ];

    protected $tmDetails = [
        'version' => 1,
        'homeCd' => [
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
        'workCd' => [
            'id' => 2,
            'version' => 2,
            'address' => [
                'id' => 2,
                'version' => 2,
                'addressLine1' => 'addressLine21',
                'addressLine2' => 'addressLine22',
                'addressLine3' => 'addressLine23',
                'addressLine4' => 'addressLine24',
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

    public function setUpAction()
    {
        $this->sut = m::mock('\Olcs\Controller\TransportManager\Details\TransportManagerDetailsDetailController')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->sm = Bootstrap::getServiceManager();
        $this->sut->setServiceLocator($this->sm);
        $this->sut->setEnabledCsrf(false);

        // mock translator and search form so we don't need a real service manager
        $this->sut->shouldReceive('getSearchForm');
        $this->sm->setService(
            'Translator',
            m::mock()
                ->shouldReceive('translate')
                ->getMock()
        );
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
            ->shouldReceive('getForm')
            ->with('TransportManager')
            ->andReturn(
                m::mock()
                ->shouldReceive('setData')
                ->andReturn(null)
                ->shouldReceive('remove')
                ->andReturn(null)
                ->getMock()
            );

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
            ->shouldReceive('getForm')
            ->with('TransportManager')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->with('csrf')
                ->shouldReceive('isValid')
                ->andReturn(true)
                ->shouldReceive('getData')
                ->andReturn($this->post)
                ->shouldReceive('setData')
                ->with($this->post)
                ->getMock()
            );

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
                    'homeCd' => 1,
                    'workCd' => 1,
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
            ->shouldReceive('save')
            ->with($this->post['work-address'])
            ->andReturn(['id' => 2])
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
                    'birthDate' => $this->post['transport-manager-details']['birthDate'],
                    'birthPlace' => $this->post['transport-manager-details']['birthPlace']
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $mockContactDetailsService = m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['homeCdId'],
                    'version' => $this->post['transport-manager-details']['homeCdVersion'],
                    'person' => 1,
                    'address' => 1,
                    'emailAddress' => $this->post['transport-manager-details']['emailAddress'],
                    'contactType' => 'ct_tm'
                ]
            )
            ->andReturn(['id' => 1])
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['workCdId'],
                    'version' => $this->post['transport-manager-details']['workCdVersion'],
                    'address' => 2,
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
                ->with('The Transport Manager has been updated successfully')
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
            ->shouldReceive('getForm')
            ->with('TransportManager')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->getMock()
            );

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

        $this->sut
            ->shouldReceive('getForm')
            ->with('TransportManager')
            ->andReturn(
                m::mock()
                ->shouldReceive('remove')
                ->shouldReceive('setData')
                ->with($this->post)
                ->shouldReceive('isValid')
                ->andReturn(true)
                ->shouldReceive('getData')
                ->andReturn($this->post)
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
                    'homeCd' => 1,
                    'workCd' => 1,
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
            ->shouldReceive('save')
            ->with($this->post['work-address'])
            ->andReturn(['id' => 2])
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
                    'birthDate' => $this->post['transport-manager-details']['birthDate'],
                    'birthPlace' => $this->post['transport-manager-details']['birthPlace']
                ]
            )
            ->andReturn(['id' => 1])
            ->getMock();

        $mockContactDetailsService = m::mock()
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['homeCdId'],
                    'version' => $this->post['transport-manager-details']['homeCdVersion'],
                    'person' => 1,
                    'address' => 1,
                    'emailAddress' => $this->post['transport-manager-details']['emailAddress'],
                    'contactType' => 'ct_tm'
                ]
            )
            ->andReturn(['id' => 1])
            ->shouldReceive('save')
            ->with(
                [
                    'id' => $this->post['transport-manager-details']['workCdId'],
                    'version' => $this->post['transport-manager-details']['workCdVersion'],
                    'address' => 2,
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
