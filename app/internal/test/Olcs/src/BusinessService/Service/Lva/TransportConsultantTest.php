<?php

/**
 * TransportConsultant.php
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

use Common\BusinessService\Response;

use OlcsTest\Bootstrap;

use Olcs\BusinessService\Service\Lva\TransportConsultant;

use Common\Service\Entity\ContactDetailsEntityService;

/**
 * Class TransportConsultantTest
 *
 * Test the transport consultant business service.
 *
 * @package OlcsTest\BusinessService\Service\Lva
 */
class TransportConsultantTest extends MockeryTestCase
{
    protected $sut = null;

    protected $sm = null;

    protected $bsm = null;

    public function setUp()
    {
        $this->sut = new TransportConsultant();

        $this->sm = Bootstrap::getServiceManager();
        $this->bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();

        $this->sut->setServiceLocator($this->sm);
        $this->sut->setBusinessServiceManager($this->bsm);
    }

    public function testProcessSave()
    {
        $params = array(
            'add-transport-consultant' => 'Y',
            'writtenPermissionToEngage' => 'N',
            'transportConsultantName' => 'test',
            'address' => array(),
            'contact' => array()
        );

        $expected = \Common\BusinessService\ResponseInterface::TYPE_SUCCESS;

        $mockAddressService = m::mock('\Common\BusinessService\BusinessServiceInterface')
            ->shouldReceive('process')
            ->with(
                array(
                    'data' => $params['address']
                )
            )->andReturn(
                m::mock()
                    ->shouldReceive('getData')
                    ->andReturn(
                        array(
                            'id' => 1
                        )
                    )->getMock()
            )->getMock();

        $mockContactDetailsService = m::mock('\Common\BusinessService\BusinessServiceInterface')
            ->shouldReceive('process')
            ->with(
                array(
                    'data' => array(
                        'contactType' => ContactDetailsEntityService::CONTACT_TYPE_TRANSPORT_CONSULTANT,
                        'writtenPermissionToEngage' => $params['writtenPermissionToEngage'],
                        'fao' => $params['transportConsultantName'],
                        'address' => 1
                    )
                )
            )->andReturn(
                m::mock()
                    ->shouldReceive('getData')
                    ->andReturn(
                        array(
                            'id' => 1
                        )
                    )->getMock()
            )->getMock();

        $mockPhoneContactService = m::mock('\Common\BusinessService\BusinessServiceInterface')
            ->shouldReceive('process')
            ->with(
                array(
                    'correspondenceId' => 1,
                    'data' => array(
                        'contact' => $params['contact']
                    )
                )
            )->andReturn(
                m::mock()
                    ->shouldReceive('getData')
                    ->andReturn(
                        array(
                            'id' => 1
                        )
                    )->getMock()
            )->getMock();

        $this->bsm->setService('Lva\Address', $mockAddressService);
        $this->bsm->setService('Lva\ContactDetails', $mockContactDetailsService);
        $this->bsm->setService('Lva\PhoneContact', $mockPhoneContactService);

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals($response->getType(), $expected);
    }

    public function testProcessUpdate()
    {
        $params = array(
            'add-transport-consultant' => 'N',
        );

        $expected = \Common\BusinessService\ResponseInterface::TYPE_SUCCESS;

        $response = $this->sut->process($params);

        $this->assertInstanceOf('\Common\BusinessService\Response', $response);
        $this->assertEquals($response->getType(), $expected);
        $this->assertEquals($response->getData()['id'], null);
    }
}
