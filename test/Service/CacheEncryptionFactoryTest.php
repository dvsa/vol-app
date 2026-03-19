<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Transfer\Service;

use Dvsa\Olcs\Transfer\Service\CacheEncryption;
use Dvsa\Olcs\Transfer\Service\CacheEncryptionFactory;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Laminas\Cache\Storage\StorageInterface;

class CacheEncryptionFactoryTest extends MockeryTestCase
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

        $cache = m::mock(StorageInterface::class);

        $mockSl = m::mock(ContainerInterface::class);
        $mockSl->shouldReceive('get')->with('Config')->andReturn($config);
        $mockSl->shouldReceive('get')->with('default-cache')->andReturn($cache);

        $sut = new CacheEncryptionFactory();
        $service = $sut->__invoke($mockSl, CacheEncryption::class);

        self::assertInstanceOf(CacheEncryption::class, $service);
        self::assertEquals('ssweb', $service->getNodeSuffix());
        self::assertEquals('nonprod/redis-ss', $service->getNodeKey());
        self::assertEquals('nonprod/redis-shared', $service->getSharedKey());
    }
}
