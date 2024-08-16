<?php

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorInterface;
use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager;
use Laminas\ServiceManager\Exception\InvalidServiceException;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager
 */
class SectionGeneratorPluginManagerTest extends MockeryTestCase
{
    /** @var  SectionGeneratorPluginManager */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new SectionGeneratorPluginManager($this->createMock(ContainerInterface::class));
    }

    public function testValidate()
    {
        $plugin = m::mock(SectionGeneratorInterface::class);

        $this->assertNull($this->sut->validate($plugin));
    }

    public function testValidateInvalid()
    {
        $this->expectException(InvalidServiceException::class);

        $this->sut->validate(null);
    }
}
