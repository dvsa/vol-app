<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\IrhpApplication;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Repository\IrhpCandidatePermit as IrhpCandidatePermitRepo;
use Dvsa\Olcs\Api\Entity\IrhpInterface;
use Dvsa\Olcs\Api\Entity\Permits\IrhpCandidatePermit;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

abstract class AbstractReviveFromUnsuccessfulTestCase extends AbstractCommandHandlerTestCase
{
    protected $applicationRepoServiceName = 'changeMe';

    protected $applicationRepoClass = 'changeMe';

    protected $sutClass = 'changeMe';

    protected $entityClass = 'changeMe';

    protected $applicationId = 47;

    protected $application;

    protected $command;

    public function setUp(): void
    {
        $this->mockRepo($this->applicationRepoServiceName, $this->applicationRepoClass);
        $this->mockRepo('IrhpCandidatePermit', IrhpCandidatePermitRepo::class);
        $this->sut = new $this->sutClass();

        $this->application = m::mock($this->entityClass);

        $this->command = m::mock(CommandInterface::class);
        $this->command->shouldReceive('getId')
            ->withNoArgs()
            ->andReturn($this->applicationId);

        $this->repoMap[$this->applicationRepoServiceName]->shouldReceive('fetchById')
            ->with($this->applicationId)
            ->andReturn($this->application);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            IrhpInterface::STATUS_UNDER_CONSIDERATION
        ];

        parent::initReferences();
    }

    public function testHandleCommand(): void
    {
        $irhpCandidatePermit1 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit1->shouldReceive('reviveFromUnsuccessful')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('save')
            ->with($irhpCandidatePermit1)
            ->once()
            ->globally()
            ->ordered();

        $irhpCandidatePermit2 = m::mock(IrhpCandidatePermit::class);
        $irhpCandidatePermit2->shouldReceive('reviveFromUnsuccessful')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap['IrhpCandidatePermit']->shouldReceive('save')
            ->with($irhpCandidatePermit2)
            ->once()
            ->globally()
            ->ordered();

        $irhpCandidatePermits = new ArrayCollection([$irhpCandidatePermit1, $irhpCandidatePermit2]);

        $this->application->shouldReceive('canBeRevivedFromUnsuccessful')
            ->withNoArgs()
            ->andReturnTrue();
        $this->application->shouldReceive('getFirstIrhpPermitApplication->getIrhpCandidatePermits')
            ->withNoArgs()
            ->andReturn($irhpCandidatePermits);
        $this->application->shouldReceive('reviveFromUnsuccessful')
            ->with($this->refData[IrhpInterface::STATUS_UNDER_CONSIDERATION])
            ->once()
            ->globally()
            ->ordered();

        $this->repoMap[$this->applicationRepoServiceName]->shouldReceive('save')
            ->with($this->application)
            ->once()
            ->globally()
            ->ordered();

        $result = $this->sut->handleCommand($this->command);

        $this->assertEquals(
            ['Application revived from unsuccessful state'],
            $result->getMessages()
        );

        $this->assertEquals(
            $this->applicationId,
            $result->getId($this->applicationRepoServiceName)
        );
    }

    public function testHandleCommandException(): void
    {
        $this->expectException(ForbiddenException::class);
        $this->expectExceptionMessage('Application cannot be revived from unsuccessful');

        $this->application->shouldReceive('canBeRevivedFromUnsuccessful')
            ->withNoArgs()
            ->andReturnFalse();

        $this->sut->handleCommand($this->command);
    }
}
