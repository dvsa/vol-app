<?php

namespace OlcsTest\Controller\Lva\Adapters;

use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Controller\Lva\Adapters\VariationPeopleAdapter;

class VariationPeopleAdapterTest extends MockeryTestCase
{
    protected $sut;

    protected $container;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = m::mock(ContainerInterface::class);

        $this->sut = new VariationPeopleAdapter($this->container);
    }

    public function testCanModify(): void
    {
        $this->assertTrue($this->sut->canModify(123));
    }
}
