<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Db\Service\Search;

use Dvsa\Olcs\Db\Service\Search\ClientFactory;
use Elastica\Client;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Psr\Container\ContainerInterface;

class ClientFactoryTest extends m\Adapter\Phpunit\MockeryTestCase
{
    public function testInvoke(): void
    {
        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn(
            ['elastic_search' => ['host' => 'google.com', 'port' => 4034]]
        );

        $sut = new ClientFactory();
        $service = $sut->__invoke($mockSl, Client::class);

        $this->assertInstanceOf(Client::class, $service);
    }

    public function testInvokeWithException(): void
    {
        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('config')->andReturn([]);

        $sut = new ClientFactory();
        $passed = false;
        try {
            $service = $sut->__invoke($mockSl, Client::class);
        } catch (InvalidServiceException $e) {
            if ($e->getMessage() === 'Elastic search config not found') {
                $passed = true;
            }
        }

        $this->assertTrue($passed, 'Expected exception not thrown or message didn\'t match');
    }
}
