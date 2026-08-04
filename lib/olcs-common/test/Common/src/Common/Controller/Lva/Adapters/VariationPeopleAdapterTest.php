<?php

declare(strict_types=1);

namespace OlcsTest\Controller\Lva\Adapters;

use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Common\Controller\Lva\Adapters\VariationPeopleAdapter;

final class VariationPeopleAdapterTest extends MockeryTestCase
{
    protected $sut;

    #[\Override]
    protected function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);

        $this->sut = new VariationPeopleAdapter($container);
    }

    public function testCanModify(): void
    {
        $this->assertTrue($this->sut->canModify(123));
    }
}
