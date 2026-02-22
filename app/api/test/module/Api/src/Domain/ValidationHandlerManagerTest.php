<?php

declare(strict_types=1);

/**
 * Validation Handler Manager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain;

use Dvsa\Olcs\Api\Domain\Validation\Handlers\HandlerInterface;
use Psr\Container\ContainerInterface;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Dvsa\Olcs\Api\Domain\ValidationHandlerManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;

/**
 * Validation Handler Manager Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class ValidationHandlerManagerTest extends MockeryTestCase
{
    /**
     * @var ValidationHandlerManager
     */
    protected $sut;

    public function setUp(): void
    {
        $container = m::mock(ContainerInterface::class);
        $this->sut = new ValidationHandlerManager($container, []);
    }

    public function testGet(): void
    {
        $mock = m::mock(HandlerInterface::class);

        $this->sut->setService('Foo', $mock);

        $this->assertSame($mock, $this->sut->get('Foo'));
    }

    public function testValidate(): void
    {
        $plugin = m::mock(HandlerInterface::class);
        $this->assertNull($this->sut->validate($plugin));
    }

    public function testValidateInvalid(): void
    {
        $this->expectException(InvalidServiceException::class);
        $this->sut->validate(null);
    }
}
