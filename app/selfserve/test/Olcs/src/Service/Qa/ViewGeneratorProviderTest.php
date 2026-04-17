<?php

declare(strict_types=1);

namespace OlcsTest\Service\Qa;

use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Olcs\Service\Qa\ViewGeneratorProvider;
use Olcs\Service\Qa\ViewGenerator\ViewGeneratorInterface;
use RuntimeException;

class ViewGeneratorProviderTest extends MockeryTestCase
{
    private $irhpApplicationRouteName = 'permits/application/question';

    private $irhpApplicationViewGenerator;

    private $viewGeneratorProvider;

    public function setUp(): void
    {
        $this->irhpApplicationViewGenerator = m::mock(ViewGeneratorInterface::class);

        $this->viewGeneratorProvider = new ViewGeneratorProvider();

        $this->viewGeneratorProvider->registerViewGenerator(
            $this->irhpApplicationRouteName,
            $this->irhpApplicationViewGenerator
        );

        $this->viewGeneratorProvider->registerViewGenerator(
            'another/route',
            m::mock(ViewGeneratorInterface::class)
        );
    }

    public function testGetByRouteName(): void
    {
        $this->assertSame(
            $this->irhpApplicationViewGenerator,
            $this->viewGeneratorProvider->getByRouteName($this->irhpApplicationRouteName)
        );
    }

    public function testGetByRouteNameNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('No view generator found for route test/route');

        $this->viewGeneratorProvider->getByRouteName('test/route');
    }
}
