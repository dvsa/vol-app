<?php

declare(strict_types=1);

namespace CommonTest\View\Helper;

use Common\Service\Helper\TranslationHelperService;
use Common\View\Helper\ImportantPrefix;
use Common\View\Helper\ImportantPrefixFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

#[\PHPUnit\Framework\Attributes\CoversClass(ImportantPrefixFactory::class)]
final class ImportantPrefixFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        $container = m::mock(ContainerInterface::class);
        $container->expects('get')->with('Helper\Translation')->andReturn(m::mock(TranslationHelperService::class));

        $this->assertInstanceOf(
            ImportantPrefix::class,
            (new ImportantPrefixFactory())->__invoke($container, ImportantPrefix::class)
        );
    }
}
