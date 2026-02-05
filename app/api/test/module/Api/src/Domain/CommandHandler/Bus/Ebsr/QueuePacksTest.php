<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\QueuePacks;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission;
use Dvsa\Olcs\Api\Entity\Queue\Queue;
use Dvsa\Olcs\Transfer\Command\Bus\Ebsr\QueuePacks as QueuePacksCmd;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use LmcRbacMvc\Identity\IdentityInterface;
use Mockery as m;

/**
 * QueuePacksTest
 */
class QueuePacksTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueuePacks();
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            EbsrSubmission::UPLOADED_STATUS,
            EbsrSubmission::SUBMITTED_STATUS,
            EbsrSubmission::NEW_SUBMISSION_TYPE
        ];

        parent::initReferences();
    }

    /**
     * Tests EBSR packs are queued correctly
     */
    public function testHandleCommand(): void
    {
        $submissionType = EbsrSubmission::NEW_SUBMISSION_TYPE;
        $cmd = QueuePacksCmd::create(['submissionType' => $submissionType]);

        $ebsrId1 = 123;
        $ebsrId2 = 456;

        $ebsrSub1 = m::mock(EbsrSubmission::class);
        $ebsrSub1->shouldReceive('getId')->once()->andReturn($ebsrId1);
        $ebsrSub1->shouldReceive('submit')->once()->with(
            $this->refData[EbsrSubmission::SUBMITTED_STATUS],
            $this->refData[$submissionType]
        );

        $ebsrSub2 = m::mock(EbsrSubmission::class);
        $ebsrSub2->shouldReceive('getId')->once()->andReturn($ebsrId2);
        $ebsrSub2->shouldReceive('submit')->once()->with(
            $this->refData[EbsrSubmission::SUBMITTED_STATUS],
            $this->refData[$submissionType]
        );

        $ebsrSubmissions = [$ebsrSub1, $ebsrSub2];

        $organisationId = 1245;
        $organisation = m::mock(OrganisationEntity::class);
        $organisation->shouldReceive('getId')->andReturn($organisationId);

        $identity = m::mock(IdentityInterface::class);
        $identity->shouldReceive('getUser->getOrganisationUsers->isEmpty')->once()->andReturn(false);
        $identity->shouldReceive('getUser->getRelatedOrganisation')->once()->andReturn($organisation);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchForOrganisationByStatus')
            ->with($organisationId, EbsrSubmission::UPLOADED_STATUS)
            ->once()
            ->andReturn($ebsrSubmissions);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('save')
            ->times(2)
            ->with(m::type(EbsrSubmission::class));

        $queueData1 = [
            'id' => $ebsrId1,
            'organisation' => $organisationId
        ];

        $queueData2 = [
            'id' => $ebsrId2,
            'organisation' => $organisationId
        ];

        $this->expectedQueueSideEffect($ebsrId1, Queue::TYPE_EBSR_PACK, $queueData1);
        $this->expectedQueueSideEffect($ebsrId2, Queue::TYPE_EBSR_PACK, $queueData2);

        $result = $this->sut->handleCommand($cmd);

        $this->assertInstanceOf(Result::class, $result);
        $this->assertEquals($result->getMessages()[0], '2 packs were queued for upload');
    }

    /**
     * Tests what happens if there are no packs for the organisation
     */
    public function testHandleCommandNoPacks(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $submissionType = EbsrSubmission::NEW_SUBMISSION_TYPE;
        $cmd = QueuePacksCmd::create(['submissionType' => $submissionType]);

        $organisationId = 1245;
        $organisation = m::mock(OrganisationEntity::class);
        $organisation->shouldReceive('getId')->andReturn($organisationId);

        $identity = m::mock(IdentityInterface::class);
        $identity->shouldReceive('getUser->getOrganisationUsers->isEmpty')->once()->andReturn(false);
        $identity->shouldReceive('getUser->getRelatedOrganisation')->once()->andReturn($organisation);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $this->repoMap['EbsrSubmission']
            ->shouldReceive('fetchForOrganisationByStatus')
            ->with($organisationId, EbsrSubmission::UPLOADED_STATUS)
            ->once()
            ->andReturn([]);

        $this->sut->handleCommand($cmd);
    }

    /**
     * Tests what happens if the organisation is missing
     */
    public function testHandleCommandNoOrganisation(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $submissionType = EbsrSubmission::NEW_SUBMISSION_TYPE;
        $cmd = QueuePacksCmd::create(['submissionType' => $submissionType]);

        $identity = m::mock(IdentityInterface::class);
        $identity->shouldReceive('getUser->getOrganisationUsers->isEmpty')->once()->andReturn(true);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $this->sut->handleCommand($cmd);
    }
}
