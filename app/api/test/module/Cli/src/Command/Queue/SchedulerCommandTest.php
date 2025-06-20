<?php

namespace Dvsa\OlcsTest\Cli\Command\Queue;

use Dvsa\Olcs\Api\Domain\CommandHandlerManager;
use Dvsa\Olcs\Cli\Command\Queue\SchedulerCommand;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;
use Olcs\Logging\Log\Logger;

class SchedulerCommandTest extends TestCase
{
    protected CommandTester $commandTester;
    protected $mockCommandHandlerManager;
    protected SchedulerCommand $sut;

    protected function setUp(): void
    {
        $this->mockCommandHandlerManager = $this->createMock(CommandHandlerManager::class);

        $this->sut = new SchedulerCommand($this->mockCommandHandlerManager);
        $this->sut->setName('queue:scheduler');

        $logWriter = new \Laminas\Log\Writer\Mock();
        $logger = new \Laminas\Log\Logger();
        $logger->addWriter($logWriter);

        Logger::setLogger($logger);

        $application = new Application();
        $application->add($this->sut);

        $this->commandTester = new CommandTester($application->find('queue:scheduler'));
    }

    public function testCommandConfiguration(): void
    {
        $this->assertEquals('queue:scheduler', $this->sut->getName());
        $this->assertEquals(
            'Continuous queue processor scheduler',
            $this->sut->getDescription()
        );
    }

    public function testGetActiveSchedulesInLocalEnvironment(): void
    {
        putenv('ENVIRONMENT_NAME=local');

        $reflection = new \ReflectionClass($this->sut);
        $method = $reflection->getMethod('getActiveSchedules');
        $method->setAccessible(true);

        $mockOutput = $this->createMock(\Symfony\Component\Console\Output\OutputInterface::class);

        $outputProperty = $reflection->getProperty('output');
        $outputProperty->setAccessible(true);
        $outputProperty->setValue($this->sut, $mockOutput);

        // Expect messages for disabled jobs
        $mockOutput->expects($this->exactly(2))
            ->method('writeln')
            ->withConsecutive(
                ["<comment>Skipping 'transxchange_consumer' (disabled for local environment)</comment>"],
                ["<comment>Skipping 'process_company_profile' (disabled for local environment)</comment>"]
            );

        $activeSchedules = $method->invoke($this->sut);

        $this->assertCount(7, $activeSchedules);

        $scheduleNames = array_column($activeSchedules, 'name');
        $this->assertNotContains('transxchange_consumer', $scheduleNames);
        $this->assertNotContains('process_company_profile', $scheduleNames);

        $this->assertContains('process_queue_general', $scheduleNames);
        $this->assertContains('process_queue_community_licences', $scheduleNames);
    }

    public function testGetActiveSchedulesInProductionEnvironment(): void
    {
        putenv('ENVIRONMENT_NAME=APP');

        $reflection = new \ReflectionClass($this->sut);
        $method = $reflection->getMethod('getActiveSchedules');
        $method->setAccessible(true);

        $mockOutput = $this->createMock(\Symfony\Component\Console\Output\OutputInterface::class);

        $outputProperty = $reflection->getProperty('output');
        $outputProperty->setAccessible(true);
        $outputProperty->setValue($this->sut, $mockOutput);

        $mockOutput->expects($this->never())
            ->method('writeln');

        $activeSchedules = $method->invoke($this->sut);

        $this->assertCount(9, $activeSchedules);

        $scheduleNames = array_column($activeSchedules, 'name');
        $this->assertContains('transxchange_consumer', $scheduleNames);
        $this->assertContains('process_company_profile', $scheduleNames);
        $this->assertContains('process_queue_general', $scheduleNames);
    }

    public function testGetActiveSchedulesWithDefaultLocalEnvironment(): void
    {
        putenv('ENVIRONMENT_NAME');

        $reflection = new \ReflectionClass($this->sut);
        $method = $reflection->getMethod('getActiveSchedules');
        $method->setAccessible(true);

        $mockOutput = $this->createMock(\Symfony\Component\Console\Output\OutputInterface::class);

        $outputProperty = $reflection->getProperty('output');
        $outputProperty->setAccessible(true);
        $outputProperty->setValue($this->sut, $mockOutput);

        $mockOutput->expects($this->exactly(2))
            ->method('writeln');

        $activeSchedules = $method->invoke($this->sut);

        $this->assertCount(7, $activeSchedules);
    }

    public function testCalculateNextRun(): void
    {
        $reflection = new \ReflectionClass($this->sut);
        $method = $reflection->getMethod('calculateNextRun');
        $method->setAccessible(true);

        $nextRun = $method->invoke($this->sut, 2);

        $this->assertInstanceOf(\DateTime::class, $nextRun);

        $this->assertGreaterThan(new \DateTime(), $nextRun);

        $minutes = (int)$nextRun->format('i');
        $this->assertEquals(0, $minutes % 2);
    }

    public function testScheduleStructure(): void
    {
        $reflection = new \ReflectionClass($this->sut);
        $property = $reflection->getProperty('schedules');
        $property->setAccessible(true);
        $schedules = $property->getValue($this->sut);

        foreach ($schedules as $schedule) {
            $this->assertArrayHasKey('name', $schedule);
            $this->assertArrayHasKey('interval', $schedule);
            $this->assertArrayHasKey('command', $schedule);
            $this->assertArrayHasKey('args', $schedule);

            $this->assertIsString($schedule['name']);
            $this->assertIsInt($schedule['interval']);
            $this->assertIsString($schedule['command']);
            $this->assertIsArray($schedule['args']);
        }

        $scheduleNames = array_column($schedules, 'name');
        $expectedSchedules = [
            'process_queue_general',
            'process_queue_community_licences',
            'process_queue_disc_generation',
            'process_queue_print',
            'process_queue_permit_generation',
            'process_queue_ecmt_accept',
            'process_queue_irhp_allocate',
            'transxchange_consumer',
            'process_company_profile'
        ];

        foreach ($expectedSchedules as $expectedSchedule) {
            $this->assertContains($expectedSchedule, $scheduleNames);
        }
    }

    protected function tearDown(): void
    {
        putenv('ENVIRONMENT_NAME');
        parent::tearDown();
    }
}
