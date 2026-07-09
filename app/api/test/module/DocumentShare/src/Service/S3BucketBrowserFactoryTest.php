<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\DocumentShare\Service;

use Aws\S3\S3Client;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowser;
use Dvsa\Olcs\DocumentShare\Service\S3BucketBrowserFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(S3BucketBrowserFactory::class)]
final class S3BucketBrowserFactoryTest extends MockeryTestCase
{
    private function container(array $documentShare): ContainerInterface
    {
        $container = m::mock(ContainerInterface::class);
        $container->shouldReceive('get')->with('config')->andReturn(['document_share' => $documentShare]);
        $container->shouldReceive('get')->with(S3Client::class)->andReturn(m::mock(S3Client::class));
        $container->shouldReceive('get')->with('Logger')->andReturn(m::mock(LoggerInterface::class));

        return $container;
    }

    public function testInvokeBuildsBucketBrowser(): void
    {
        $sut = new S3BucketBrowserFactory();

        $browser = $sut->__invoke($this->container(['s3' => ['bucket' => 'b']]), S3BucketBrowser::class);

        $this->assertInstanceOf(S3BucketBrowser::class, $browser);
    }

    public function testInvokeBuildsBucketBrowserWhenBucketMissing(): void
    {
        // The factory no longer throws on a missing bucket — that would 500 at handler construction,
        // before the feature-toggle and system-admin gates run. The browser fails closed at call
        // time instead (see S3BucketBrowserTest), so a disabled/unauthorised request is handled
        // cleanly rather than leaking a config error.
        $sut = new S3BucketBrowserFactory();

        $browser = $sut->__invoke($this->container(['s3' => []]), S3BucketBrowser::class);

        $this->assertInstanceOf(S3BucketBrowser::class, $browser);
    }
}
