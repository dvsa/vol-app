<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain;

use Dvsa\Olcs\Api\Domain\QueryPartial\QueryPartialInterface;
use Dvsa\Olcs\Api\Domain\QueryPartialServiceManager;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

class QueryPartialServiceManagerTest extends MockeryTestCase
{
    protected QueryPartialServiceManager $sut;

    public function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        $this->sut = new QueryPartialServiceManager($container, []);
    }

    public function testValidate(): void
    {
        $this->assertNull($this->sut->validate(m::mock(QueryPartialInterface::class)));
    }
}
