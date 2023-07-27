<?php

namespace OlcsTest\Controller\Lva\Factory\Adapter;

use Common\Service\Cqrs\Command\CommandService;
use Common\Service\Cqrs\Query\CachingQueryService;
use Common\Service\Lva\VariationLvaService;
use Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder;
use Interop\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters;
use Olcs\Controller\Lva\Factory\Adapter as AdapterFactory;
use Laminas\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Olcs\Controller\Lva\Factory\Adapter\VariationTransportManagerAdapterFactory
 * @covers Olcs\Controller\Lva\Factory\Adapter\LicenceTransportManagerAdapterFactory
 *
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class TransportManagerAdapterFactoryTest extends MockeryTestCase
{
    /** @var ContainerInterface|\Mockery\MockInterface */
    protected $container;

    public function setUp(): void
    {
        $this->container = m::mock(ServiceLocatorInterface::class);

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

    public function testCreateServiceLicence()
    {
        $factory = new AdapterFactory\LicenceTransportManagerAdapterFactory();

        static::assertInstanceOf(
            Adapters\LicenceTransportManagerAdapter::class,
            $factory->createService($this->container)
        );
    }

    public function testCreateServiceVariation()
    {
        $factory = new AdapterFactory\VariationTransportManagerAdapterFactory();

        static::assertInstanceOf(
            Adapters\VariationTransportManagerAdapter::class,
            $factory->createService($this->container)
        );
    }
}
