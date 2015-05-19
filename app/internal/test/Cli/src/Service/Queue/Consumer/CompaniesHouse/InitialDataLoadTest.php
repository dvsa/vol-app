<?php

/**
 * Companies House Initial Data Load Queue Consumer Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
namespace CliTest\Service\Queue\Consumer\CompaniesHouse;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use OlcsTest\Bootstrap;
use Cli\Service\Queue\Consumer\CompaniesHouse\InitialDataLoad;
use Common\BusinessService\Response;

/**
 * Companies House Initial Data Load Queue Consumer Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class InitialDataLoadTest extends MockeryTestCase
{
    protected $sut;
    protected $sm;
    protected $bsm;

    public function setUp()
    {
        $this->sm = Bootstrap::getServiceManager();

        $this->bsm = m::mock('\Common\BusinessService\BusinessServiceManager')->makePartial();
        $this->sm->setService('BusinessServiceManager', $this->bsm);

        $this->sut = new InitialDataLoad();
        $this->sut->setServiceLocator($this->sm);
    }

    /**
     * @dataProvider successProvider
     */
    public function testProcessMessageSuccess($response, $expected)
    {
        $item = [
            'id' => 99,
            'options' => '{"companyNumber":"01234567"}',
        ];

        $mockBusinessService = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $this->bsm->setService('Cli\CompaniesHouseLoad', $mockBusinessService);

        $mockQueue = m::mock();
        $this->sm->setService('Entity\Queue', $mockQueue);

        $mockBusinessService
            ->shouldReceive('process')
            ->with(['companyNumber' => "01234567"])
            ->once()
            ->andReturn($response);

        $mockQueue
            ->shouldReceive('complete')
            ->once()
            ->with($item);

        $result = $this->sut->processMessage($item);

        $this->assertEquals($expected, $result);
    }

    public function successProvider()
    {
        return [
            'no message' => [
                new Response(Response::TYPE_SUCCESS),
                'Successfully processed message: 99 {"companyNumber":"01234567"}',
            ],
            'with message' => [
                new Response(Response::TYPE_SUCCESS, [], 'foo'),
                'Successfully processed message: 99 {"companyNumber":"01234567"} foo',
            ],
        ];
    }

    public function testProcessMessageFailure()
    {
        $item = [
            'id' => 99,
            'options' => '{"companyNumber":"01234567"}',
        ];

        $mockBusinessService = m::mock('\Common\BusinessService\BusinessServiceInterface');
        $this->bsm->setService('Cli\CompaniesHouseLoad', $mockBusinessService);

        $mockQueue = m::mock();
        $this->sm->setService('Entity\Queue', $mockQueue);

        $mockBusinessService
            ->shouldReceive('process')
            ->with(['companyNumber' => "01234567"])
            ->once()
            ->andReturn(new Response(Response::TYPE_FAILED, [], 'epic fail'));

        $mockQueue
            ->shouldReceive('failed')
            ->once()
            ->with($item);

        $expected = 'Failed to process message: 99 {"companyNumber":"01234567"} epic fail';

        $result = $this->sut->processMessage($item);

        $this->assertEquals($expected, $result);
    }
}
