<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\Factory\Adapter;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Lva\VariationLvaService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters;
use Olcs\Controller\Lva\Factory\Adapter as AdapterFactory;

/**
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Controller\Lva\Factory\Adapter\VariationTransportManagerAdapterFactory::class)]
#[\PHPUnit\Framework\Attributes\CoversClass(\Olcs\Controller\Lva\Factory\Adapter\LicenceTransportManagerAdapterFactory::class)]
final class TransportManagerAdapterFactoryTest extends MockeryTestCase
{
    /** @var ContainerInterface|\Mockery\MockInterface */
    protected $container;

    #[\Override]
    public function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);

        $closure = function ($class) {
            $map = [
                AnnotationBuilder::class => m::mock(\Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder::class),
                CachingQueryService::class => m::mock(\Common\Service\Cqrs\Query\CachingQueryService::class),
                CommandService::class => m::mock(\Common\Service\Cqrs\Command\CommandService::class),
                VariationLvaService::class => m::mock(VariationLvaService::class)
            ];

            return $map[$class];
        };
        $this->container->shouldReceive('get')->andReturnUsing($closure);
    }

    public function testInvokeLicence(): void
    {
        $factory = new AdapterFactory\LicenceTransportManagerAdapterFactory();

        $this->assertInstanceOf(Adapters\LicenceTransportManagerAdapter::class, $factory->__invoke($this->container, Adapters\LicenceTransportManagerAdapter::class));
    }

    public function testInvokeVariation(): void
    {
        $factory = new AdapterFactory\VariationTransportManagerAdapterFactory();

        $this->assertInstanceOf(Adapters\VariationTransportManagerAdapter::class, $factory->__invoke($this->container, Adapters\VariationTransportManagerAdapter::class));
    }
}
