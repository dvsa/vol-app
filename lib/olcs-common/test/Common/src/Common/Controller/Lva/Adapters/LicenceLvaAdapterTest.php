<?php

namespace CommonTest\Common\Controller\Lva\Adapters;

use Common\Form\Form;
use Psr\Container\ContainerInterface;
use Laminas\Form\ElementInterface;
use Laminas\Mvc\Controller\AbstractController;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Controller\Lva\Adapters\LicenceLvaAdapter;

class LicenceLvaAdapterTest extends MockeryTestCase
{
    protected $sut;

    protected $container;

    protected $controller;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);

        $this->controller = m::mock(AbstractController::class);

        $this->sut = new LicenceLvaAdapter($this->container);
        $this->sut->setController($this->controller);
    }

    public function testAlterForm(): void
    {
        $mockForm = m::mock(Form::class);

        $mockForm->shouldReceive('get')
            ->with('form-actions')
            ->andReturn(
                m::mock(ElementInterface::class)
                ->shouldReceive('remove')
                ->with('saveAndContinue')
                ->getMock()
            );

        $this->assertNull($this->sut->alterForm($mockForm));
    }
}
