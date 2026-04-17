<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Application\UpdateApplicationCompletion as UpdateApplicationCompletionCmd;
use Mockery as m;

abstract class AbstractUpdateApplicationTestCase extends AbstractCommandHandlerTestCase
{
    protected int $applicationId = 888;
    protected int $version = 999;
    protected string $handlerClass = '';
    protected string $commandClass = '';
    protected string $confirmationMessage = '';
    protected array $commandData = [];
    protected array $sections = [];
    private const string APP_COMPLETION_RESULT = 'app completion message: %s';

    public function setUp(): void
    {
        $this->sut = new $this->handlerClass();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    protected function setupCommand(): CommandInterface
    {
        $fixedData = [
            'id' => $this->applicationId,
            'version' => $this->version,
        ];

        $commandData = $fixedData + $this->commandData;

        /** @var CommandInterface $command */
        $command = $this->commandClass::create($commandData);
        return $command;
    }

    protected function setupApplication(): m\MockInterface&m\LegacyMockInterface
    {
        return m::mock(ApplicationEntity::class);
    }

    public function testHandleCommand(): void
    {
        $application = $this->setupApplication();
        $command = $this->setupCommand();

        $this->repoMap['Application']->expects('fetchById')
            ->with($this->applicationId, Query::HYDRATE_OBJECT, $this->version)
            ->andReturn($application);

        $this->repoMap['Application']->expects('save')->with($application);

        foreach ($this->sections as $section) {
            $applicationCompletionResult = new Result();
            $applicationCompletionResult->addMessage(
                sprintf(self::APP_COMPLETION_RESULT, $section)
            );

            $this->expectedSideEffect(
                UpdateApplicationCompletionCmd::class,
                ['id' => $this->applicationId, 'section' => $section],
                $applicationCompletionResult
            );
        }

        $result = $this->sut->handleCommand($command);
        $this->performAssertions($result);
    }

    protected function performAssertions(Result $result): void
    {
        $messages = $result->getMessages();

        foreach ($this->sections as $section) {
            $this->assertContains(sprintf(self::APP_COMPLETION_RESULT, $section), $messages);
        }

        $this->assertContains($this->confirmationMessage, $messages);
        $this->assertEquals($this->applicationId, $result->getId('Application'));
    }
}
