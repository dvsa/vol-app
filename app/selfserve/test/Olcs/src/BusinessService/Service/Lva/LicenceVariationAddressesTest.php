<?php

/**
 * Licence / Variation Addresses Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
namespace OlcsTest\BusinessService\Service\Lva;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\BusinessService\Service\Lva\LicenceVariationAddresses;
use Common\BusinessService\Response;
use Common\Service\Data\CategoryDataService;

/**
 * Licence / Variation Addresses Test
 *
 * @author Nick Payne <nick.payne@valtech.co.uk>
 */
class LicenceVariationAddressesTest extends MockeryTestCase
{
    protected $bsm;

    public function setUp()
    {
        $this->bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();

        $this->sut = new LicenceVariationAddresses();
        $this->sut->setBusinessServiceManager($this->bsm);
    }

    public function testProcessWithBadAppAddressesResponse()
    {
        $data = [
            'originalData' => 'foo',
            'data' => 'bar'
        ];

        $dirtyData = [
            'original' => 'foo',
            'updated' => 'bar'
        ];

        $dirtyResponse = new Response();
        $badResponse = new Response();
        $badResponse->setType(Response::TYPE_FAILED);

        $this->bsm->shouldReceive('get')
            ->with('Lva\ApplicationAddresses')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($data)
                ->andReturn($badResponse)
                ->getMock()
            );

        $response = $this->sut->process($data);

        $this->assertEquals($response, $badResponse);
        $this->assertEquals(Response::TYPE_FAILED, $response->getType());
        $this->assertEquals([], $response->getData());
    }

    public function testProcessWithSuccessfulResponseButNoChanges()
    {
        $data = [
            'originalData' => 'foo',
            'data' => 'bar'
        ];

        $addressResponse = new Response();
        $addressResponse->setType(Response::TYPE_SUCCESS);
        $addressResponse->setData(['hasChanged' => false]);

        $this->bsm->shouldReceive('get')
            ->with('Lva\ApplicationAddresses')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($data)
                ->andReturn($addressResponse)
                ->getMock()
            );

        $response = $this->sut->process($data);

        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
        $this->assertEquals(['hasChanged' => false], $response->getData());
    }

    public function testProcessWithSuccessfulResponseButBadTaskResponse()
    {
        $data = [
            'originalData' => 'foo',
            'data' => 'bar'
        ];

        $addressResponse = new Response();
        $addressResponse->setType(Response::TYPE_SUCCESS);
        $addressResponse->setData(['hasChanged' => true]);

        $badResponse = new Response();
        $badResponse->setType(Response::TYPE_FAILED);

        $this->bsm->shouldReceive('get')
            ->with('Lva\ApplicationAddresses')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($data)
                ->andReturn($addressResponse)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Lva\AddressesChangeTask')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($data)
                ->andReturn($badResponse)
                ->getMock()
            );

        $response = $this->sut->process($data);

        $this->assertEquals(Response::TYPE_FAILED, $response->getType());
        $this->assertEquals([], $response->getData());
    }

    public function testProcessWithSuccessfulResponseAndSuccessfulTaskResponse()
    {
        $data = [
            'originalData' => 'foo',
            'data' => 'bar'
        ];

        $addressResponse = new Response();
        $addressResponse->setType(Response::TYPE_SUCCESS);
        $addressResponse->setData(['hasChanged' => true]);

        $taskResponse = new Response();
        $taskResponse->setType(Response::TYPE_SUCCESS);

        $this->bsm->shouldReceive('get')
            ->with('Lva\ApplicationAddresses')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($data)
                ->andReturn($addressResponse)
                ->getMock()
            )
            ->shouldReceive('get')
            ->with('Lva\AddressesChangeTask')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($data)
                ->andReturn($taskResponse)
                ->getMock()
            );

        $response = $this->sut->process($data);

        $this->assertEquals(Response::TYPE_SUCCESS, $response->getType());
        $this->assertEquals(['hasChanged' => true], $response->getData());
    }
}
