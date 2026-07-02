<?php

namespace CommonTest\Service\Helper;

use Common\Service\Utility\HtmlPurifierFactory;
use HTMLPurifier;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class HtmlPurifierFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = m::mock(ContainerInterface::class);

        $container->expects('get')
            ->with('Config')
            ->andReturn(['html-purifier-cache-dir' => 'path']);

        static::assertInstanceOf(
            HTMLPurifier::class,
            (new HtmlPurifierFactory())->__invoke($container, HTMLPurifier::class)
        );
    }
}
