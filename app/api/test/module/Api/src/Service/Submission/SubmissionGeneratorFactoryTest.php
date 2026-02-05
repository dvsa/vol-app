<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Submission;

use Dvsa\Olcs\Api\Service\Submission\Sections\SectionGeneratorPluginManager;
use Dvsa\Olcs\Api\Service\Submission\SubmissionGenerator;
use Dvsa\Olcs\Api\Service\Submission\SubmissionGeneratorFactory;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Psr\Container\ContainerInterface;

/**
 * @covers Dvsa\Olcs\Api\Service\Submission\SubmissionGeneratorFactory
 */
class SubmissionGeneratorFactoryTest extends MockeryTestCase
{
    public function testInvoke(): void
    {
        /** @var  \Laminas\ServiceManager\ServiceLocatorInterface $mockSl */
        $mockSl = m::mock(ContainerInterface::class)
            ->shouldReceive('get')
            ->andReturnUsing(
                function ($class) {
                    $map = [
                        SectionGeneratorPluginManager::class => m::mock(SectionGeneratorPluginManager::class),
                        'config' => [
                            'submissions' => [],
                        ]
                    ];

                    return $map[$class];
                }
            )
            ->getMock();

        $actual = (new SubmissionGeneratorFactory())->__invoke($mockSl, SubmissionGenerator::class);

        static::assertInstanceOf(SubmissionGenerator::class, $actual);
    }
}
