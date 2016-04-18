<?php

namespace OlcsTest\Controller\Lva\Factory\Adapter;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Controller\Lva\Adapters;
use Olcs\Controller\Lva\Factory\Adapter as AdapterFactory;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * @covers Olcs\Controller\Lva\Factory\Adapter\VariationTransportManagerAdapterFactory
 * @covers Olcs\Controller\Lva\Factory\Adapter\LicenceTransportManagerAdapterFactory
 * 
 * @author Dmitry Golubev <dmitrij.golubev@valtech.co.uk>
 */
class TransportManagerAdapterFactoryTest extends MockeryTestCase
{
    /** @var ServiceLocatorInterface|\Mockery\MockInterface */
    protected $sm;

    public function setUp()
    {
        $this->sm = m::mock(ServiceLocatorInterface::class);

        $closure = function ($class) {
            $map = [
                'TransferAnnotationBuilder' => m::mock(\Dvsa\Olcs\Transfer\Util\Annotation\AnnotationBuilder::class),
                'QueryService' => m::mock(\Common\Service\Cqrs\Query\CachingQueryService::class),
                'CommandService' => m::mock(\Common\Service\Cqrs\Command\CommandService::class),
                'Lva\Variation' => m::mock(\Common\Service\Lva\VariationLvaService::class),
            ];

            return $map[$class];
        };
        $this->sm->shouldReceive('get')->andReturnUsing($closure);
    }

    public function testCreateServiceLicence()
    {
        $factory = new AdapterFactory\LicenceTransportManagerAdapterFactory();

        static::assertInstanceOf(
            Adapters\LicenceTransportManagerAdapter::class,
            $factory->createService($this->sm)
        );
    }

    public function testCreateServiceVariation()
    {
        $factory = new AdapterFactory\VariationTransportManagerAdapterFactory();

        static::assertInstanceOf(
            Adapters\VariationTransportManagerAdapter::class,
            $factory->createService($this->sm)
        );
    }
}
