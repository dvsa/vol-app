<?php

declare(strict_types=1);

namespace CommonTest\Common\Controller\Lva\Adapters;

use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Controller\Lva\Adapters\GenericBusinessTypeAdapter;

final class GenericBusinessTypeAdapterTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        $this->sut = new GenericBusinessTypeAdapter($container);
    }

    public function testAlterFormIsNoOp(): void
    {
        $this->assertNull($this->sut->alterFormForOrganisation(m::mock(\Laminas\Form\Form::class), 123));
    }
}
