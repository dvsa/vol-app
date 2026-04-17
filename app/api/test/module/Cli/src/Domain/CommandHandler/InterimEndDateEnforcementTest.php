<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Cli\Domain\CommandHandler;

use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Service\EventHistory\Creator;
use Dvsa\Olcs\Cli\Domain\CommandHandler\InterimEndDateEnforcement;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use Olcs\Logging\Log\Logger;

class InterimEndDateEnforcementTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new InterimEndDateEnforcement();

        $this->mockRepo('Application', Repository\Application::class);

        $this->mockedSmServices = [
            Creator::class => m::mock(Creator::class)
        ];

        $logger = new \Dvsa\OlcsTest\SafeLogger();
        $logger->addWriter(new \Laminas\Log\Writer\Mock());
        Logger::setLogger($logger);

        parent::setUp();
    }

    public function testHandleCommandWithMatchingApplications(): void
    {
        $applicationRepo = $this->repoMap['Application'];

        $applications = array_fill(0, 3, $this->createMockApplication());

        $applicationRepo->shouldReceive('fetchOpenApplicationsWhereInterimInForceAndInterimEndDateIsPast')->andReturn($applications);

        foreach ($applications as $application) {
            $applicationRepo->shouldReceive('save')->with($application)->once();
            $this->mockedSmServices[Creator::class]->shouldReceive('create')->withSomeOfArgs($application)->once();
        }

        $response = $this->sut->handleCommand(\Dvsa\Olcs\Cli\Domain\Command\InterimEndDateEnforcement::create([]));

        $this->assertFalse($response->getFlag('dryrun'));
        $this->assertEquals(count($applications), $response->getFlag('identified_count'));
        $this->assertContains('Complete', $response->getMessages());
    }

    public function testHandleCommandWithNoMatchingApplications(): void
    {
        $applicationRepo = $this->repoMap['Application'];

        $applications = [];

        $applicationRepo->shouldReceive('fetchOpenApplicationsWhereInterimInForceAndInterimEndDateIsPast')->andReturn($applications);

        $applicationRepo->shouldReceive('save')->never();
        $this->mockedSmServices[Creator::class]->shouldReceive('create')->never();

        $response = $this->sut->handleCommand(\Dvsa\Olcs\Cli\Domain\Command\InterimEndDateEnforcement::create([]));

        $this->assertEquals(0, $response->getFlag('identified_count'));
        $this->assertContains('Complete', $response->getMessages());
    }

    public function testHandleCommandWithMatchingApplicationsInDryRunMakesNoSavesOrEvents(): void
    {
        $applicationRepo = $this->repoMap['Application'];

        $applications = array_fill(0, 3, $this->createMockApplication());

        $applicationRepo->shouldReceive('fetchOpenApplicationsWhereInterimInForceAndInterimEndDateIsPast')->andReturn($applications);

        $applicationRepo->shouldReceive('save')->never();
        $this->mockedSmServices[Creator::class]->shouldReceive('create')->never();

        $response = $this->sut->handleCommand(\Dvsa\Olcs\Cli\Domain\Command\InterimEndDateEnforcement::create([
            'dryRun' => true,
        ]));

        $this->assertTrue($response->getFlag('dryrun'));
        $this->assertEquals(count($applications), $response->getFlag('identified_count'));
        $this->assertContains('Complete', $response->getMessages());
    }

    private function createMockApplication(): m\MockInterface
    {
        return
            m::mock(Application::class)
                ->shouldReceive('setInterimStatus')
                ->andReturn(null)
                ->getMock()
                ->shouldReceive('getId')
                ->andReturn(1)
                ->getMock();
    }
}
