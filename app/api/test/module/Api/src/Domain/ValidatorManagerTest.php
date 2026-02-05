<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain;

use Dvsa\Olcs\Api\Domain\Validation\Validators\ValidatorInterface;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\ValidatorManager;

class ValidatorManagerTest extends MockeryTestCase
{
    /**
     * @var ValidatorManager
     */
    protected $sut;

    public function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        $this->sut = new ValidatorManager($container, []);
    }

    public function testGet(): void
    {
        $mock = m::mock(ValidatorInterface::class);

        $this->sut->setService('Foo', $mock);

        $this->assertSame($mock, $this->sut->get('Foo'));
    }

    public function testValidate(): void
    {
        $plugin = m::mock(ValidatorInterface::class);
        $this->assertNull($this->sut->validate($plugin));
    }
}
