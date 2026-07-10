<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Service;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\Olcs\Transfer\Service\CacheEncryptionFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Cache\CacheItemPoolInterface;

final class CacheEncryptionFactoryTest extends MockeryTestCase
{
    public function testInvokeNoConfig()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage(CacheEncryptionFactory::MISSING_CONFIG);
        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn([]);

        $sut = new CacheEncryptionFactory();
        $sut->__invoke($mockSl, CacheEncryption::class);
    }

    public function testInvoke()
    {
        $config = [
            'cache-encryption' => [
                'node_suffix' => 'ssweb',
                'secrets' => [
                    'node' => 'nonprod/redis-ss',
                    'shared' => 'nonprod/redis-shared',
                ],
            ],
        ];

        $cache = m::mock(CacheItemPoolInterface::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($config);
        $mockSl->shouldReceive('get')->with('default-cache')->andReturn($cache);

        $sut = new CacheEncryptionFactory();
        $service = $sut->__invoke($mockSl, CacheEncryption::class);

        $this->assertInstanceOf(CacheEncryption::class, $service);
        $this->assertSame('ssweb', $service->getNodeSuffix());
        $this->assertSame('nonprod/redis-ss', $service->getNodeKey());
        $this->assertSame('nonprod/redis-shared', $service->getSharedKey());
    }
}
