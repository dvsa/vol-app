<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission\Sections;

use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager;
use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManagerFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManagerFactory
 */
class SectionGeneratorPluginManagerFactoryTest extends MockeryTestCase
{
    public function testCanCreateServiceWithName(): void
    {
        /** @var  \Laminas\ServiceManager\ServiceLocatorInterface $mockSl */
        $mockSl = m::mock(ContainerInterface::class)
            ->shouldReceive('get')
            ->with('config')
            ->andReturn(
                [
                    'submissions' => [
                        'sections' => [],
                    ],
                ]
            )
            ->getMock();

        $actual = (new SectionGeneratorPluginManagerFactory())->__invoke($mockSl, SectionGeneratorPluginManager::class);

        static::assertInstanceOf(SectionGeneratorPluginManager::class, $actual);
    }
}
