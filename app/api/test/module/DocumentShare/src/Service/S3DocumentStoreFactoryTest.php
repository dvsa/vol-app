<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Service;

use Aws\S3\S3Client;
use Dvsa\Olcs\DocumentShare\Service\S3DocumentStore;
use Dvsa\Olcs\DocumentShare\Service\S3DocumentStoreFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use RuntimeException;

#[\PHPUnit\Framework\Attributes\CoversClass(S3DocumentStoreFactory::class)]
class S3DocumentStoreFactoryTest extends MockeryTestCase
{
    private function container(array $documentShare): ContainerInterface
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->andReturn(['document_share' => $documentShare]);
        $container->shouldReceive('get')->with(S3Client::class)->andReturn(m::mock(S3Client::class));
        $container->shouldReceive('get')->with('Logger')->andReturn(m::mock(LoggerInterface::class));

        return $container;
    }

    public function testInvokeBuildsS3DocumentStore(): void
    {
        $sut = new S3DocumentStoreFactory();

        $store = $sut->__invoke($this->container(['s3' => ['bucket' => 'b', 'key_prefix' => 'p']]), S3DocumentStore::class);

        $this->assertInstanceOf(S3DocumentStore::class, $store);
    }

    public function testInvokeWorksWithoutKeyPrefix(): void
    {
        $sut = new S3DocumentStoreFactory();

        $store = $sut->__invoke($this->container(['s3' => ['bucket' => 'b']]), S3DocumentStore::class);

        $this->assertInstanceOf(S3DocumentStore::class, $store);
    }

    public function testInvokeThrowsWhenBucketMissing(): void
    {
        $sut = new S3DocumentStoreFactory();

        $this->expectException(RuntimeException::class);

        $sut->__invoke($this->container(['s3' => ['key_prefix' => 'p']]), S3DocumentStore::class);
    }
}
