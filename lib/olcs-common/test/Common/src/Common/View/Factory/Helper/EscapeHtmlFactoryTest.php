<?php

namespace CommonTest\View\Factory\Helper;

use Common\View\Factory\Helper\EscapeHtmlFactory;
use Common\View\Helper\EscapeHtml;
use HTMLPurifier;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase as TestCase;

class EscapeHtmlFactoryTest extends TestCase
{
    public function testInvoke(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')
            ->andReturnUsing(
                static function ($class) {
                    $map = [
                        'HtmlPurifier' => m::mock(HtmlPurifier::class)
                    ];
                    return $map[$class];
                }
            );

        static::assertInstanceOf(
            EscapeHtml::class,
            (new EscapeHtmlFactory())->__invoke($container, EscapeHtml::class)
        );
    }
}
