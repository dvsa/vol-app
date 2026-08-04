<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Service;

use Aws\S3\S3Client;
use Dvsa\Olcs\DocumentShare\Service\DocumentStoreFactory;
use Dvsa\Olcs\DocumentShare\Service\S3DocumentStore;
use Dvsa\Olcs\DocumentShare\Service\WebDavClient;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(DocumentStoreFactory::class)]
final class DocumentStoreFactoryTest extends MockeryTestCase
{
    private function container(?string $backend): ContainerInterface
    {
        $documentShare = [
            'http' => [],
            'client' => [
                'workspace' => 'olcs',
                'username' => 'u',
                'password' => 'p',
                'webdav_baseuri' => 'http://webdav.test/',
            ],
            's3' => ['bucket' => 'b', 'key_prefix' => 'p'],
        ];

        if ($backend !== null) {
            $documentShare['backend'] = $backend;
        }

        $config = ['document_share' => $documentShare];

        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->andReturn($config);
        $container->shouldReceive('get')->with('Configuration')->andReturn($config);
        $container->shouldReceive('get')->with('Logger')->andReturn(m::mock(LoggerInterface::class));
        $container->shouldReceive('get')->with(S3Client::class)->andReturn(m::mock(S3Client::class));

        return $container;
    }

    public function testReturnsS3DocumentStoreWhenBackendIsS3(): void
    {
        $sut = new DocumentStoreFactory();

        $store = $sut->__invoke($this->container('s3'), 'ContentStore');

        $this->assertInstanceOf(S3DocumentStore::class, $store);
    }

    public function testReturnsWebDavClientWhenBackendIsWebdav(): void
    {
        $sut = new DocumentStoreFactory();

        $store = $sut->__invoke($this->container('webdav'), 'ContentStore');

        $this->assertInstanceOf(WebDavClient::class, $store);
    }

    public function testDefaultsToWebDavClientWhenBackendNotSet(): void
    {
        $sut = new DocumentStoreFactory();

        $store = $sut->__invoke($this->container(null), 'ContentStore');

        $this->assertInstanceOf(WebDavClient::class, $store);
    }
}
