<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Permits\Checkable;

use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Service\Permits\Checkable\CheckableApplicationInterface;
use Dvsa\Olcs\Api\Service\Permits\Checkable\CreateTaskCommandFactory;
use Dvsa\Olcs\Api\Service\Permits\Checkable\CreateTaskCommandGenerator;
use Dvsa\Olcs\Api\Service\Permits\Checkable\SubmissionTaskProperties;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * CreateTaskCommandGeneratorTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class CreateTaskCommandGeneratorTest extends MockeryTestCase
{
    public function testGenerate(): void
    {
        $submissionTaskDescription = 'submission task description';
        $camelCaseEntityName = 'camelCaseEntityName';
        $applicationId = 35;
        $licenceId = 47;

        $application = m::mock(CheckableApplicationInterface::class);
        $application->shouldReceive('getSubmissionTaskDescription')
            ->withNoArgs()
            ->andReturn($submissionTaskDescription);
        $application->shouldReceive('getCamelCaseEntityName')
            ->withNoArgs()
            ->andReturn($camelCaseEntityName);
        $application->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($applicationId);
        $application->shouldReceive('getLicence->getId')
            ->withNoArgs()
            ->andReturn($licenceId);

        $createTaskCommand = CreateTask::create([]);

        $expectedParams = [
            'category' => SubmissionTaskProperties::CATEGORY,
            'subCategory' => SubmissionTaskProperties::SUBCATEGORY,
            'description' => $submissionTaskDescription,
            $camelCaseEntityName => $applicationId,
            'licence' => $licenceId,
        ];

        $createTaskCommandFactory = m::mock(CreateTaskCommandFactory::class);
        $createTaskCommandFactory->shouldReceive('create')
            ->with($expectedParams)
            ->once()
            ->andReturn($createTaskCommand);

        $createTaskCommandGenerator = new CreateTaskCommandGenerator($createTaskCommandFactory);

        $this->assertSame(
            $createTaskCommand,
            $createTaskCommandGenerator->generate($application)
        );
    }
}
