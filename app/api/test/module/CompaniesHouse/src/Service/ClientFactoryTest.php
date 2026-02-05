<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\CompaniesHouse\Service;

use Dvsa\Olcs\CompaniesHouse\Service\Client;
use Dvsa\Olcs\CompaniesHouse\Service\ClientFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class ClientFactoryTest extends MockeryTestCase
{
    /** @var  ClientFactory */
    protected $sut;
    private $sl;

    public function setUp(): void
    {
        $this->sl = m::mock(ContainerInterface::class);
        $this->sut = new ClientFactory();
    }

    public function testOptionsMissing(): void
    {
        //  expect
        $this->expectException(\RuntimeException::class);

        //  call
        $this->sl->shouldReceive('get')->with('Configuration')->once()->andReturn([]);

        $this->sut->__invoke($this->sl, Client::class, null);
    }

    public function testOptionsBaseUriMissing(): void
    {
        //  expect
        $this->expectException(\RuntimeException::class);

        //  call
        $this->sl->shouldReceive('get')->with('Configuration')->once()->andReturn(
            [
                'companies_house' => [
                    'http' => [],
                    'client' => [],
                    'auth' => []
                ]
            ]
        );

        $this->sut->__invoke($this->sl, Client::class, null);
    }

    public function testOptions(): void
    {
        $this->sl->shouldReceive('get')->with('Configuration')->once()->andReturn(
            [
                'companies_house' => [
                    'http' => [
                        'foo' => 'bar',
                    ],
                    'client' => [
                        'baseuri' => 'http://companies-house-api/',
                    ],
                    'auth' => [
                        'username' => 'user',
                        'password' => 'secret',
                    ]
                ]
            ]
        );

        $service = $this->sut->__invoke($this->sl, Client::class);
        static::assertInstanceOf(Client::class, $service);
    }
}
