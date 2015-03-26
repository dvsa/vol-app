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

    public function testProcessWithBadDirtyAddressResponse()
    {
        $data = [
            'originalData' => 'foo',
            'data' => 'bar'
        ];

        $dirtyData = [
            'original' => 'foo',
            'updated' => 'bar'
        ];

        $badResponse = new Response();
        $badResponse->setType(Response::TYPE_FAILED);

        $this->bsm->shouldReceive('get')
            ->with('Lva\DirtyAddresses')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->with($dirtyData)
                ->andReturn($badResponse)
                ->getMock()
            );

        $response = $this->sut->process($data);

        $this->assertEquals($response, $badResponse);
        $this->assertEquals(Response::TYPE_FAILED, $response->getType());
        $this->assertEquals([], $response->getData());
    }

    public function testProcessWithBadChangeTaskResponse()
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
        $dirtyResponse->setType(Response::TYPE_SUCCESS);
        $dirtyResponse->setData(['dirtyFieldsets' => 1]);

        $badResponse = new Response();
        $badResponse->setType(Response::TYPE_FAILED);

        $this->bsm->shouldReceive('get')
            ->with('Lva\DirtyAddresses')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($dirtyData)
                ->andReturn($dirtyResponse)
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

        $this->assertEquals($response, $badResponse);
        $this->assertEquals(Response::TYPE_FAILED, $response->getType());
        $this->assertEquals([], $response->getData());
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
        $dirtyResponse->setType(Response::TYPE_SUCCESS);
        $dirtyResponse->setData(['dirtyFieldsets' => 5]);

        $taskResponse = new Response();
        $taskResponse->setType(Response::TYPE_SUCCESS);

        $badResponse = new Response();
        $badResponse->setType(Response::TYPE_FAILED);

        $this->bsm->shouldReceive('get')
            ->with('Lva\DirtyAddresses')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($dirtyData)
                ->andReturn($dirtyResponse)
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
            )
            ->shouldReceive('get')
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

    public function testProcessWithSuccessfulResponse()
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
        $dirtyResponse->setType(Response::TYPE_SUCCESS);
        $dirtyResponse->setData(['dirtyFieldsets' => 0]);

        $addressResponse = new Response();
        $addressResponse->setType(Response::TYPE_SUCCESS);

        $this->bsm->shouldReceive('get')
            ->with('Lva\DirtyAddresses')
            ->andReturn(
                m::mock()
                ->shouldReceive('process')
                ->once()
                ->with($dirtyData)
                ->andReturn($dirtyResponse)
                ->getMock()
            )
            ->shouldReceive('get')
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
}
