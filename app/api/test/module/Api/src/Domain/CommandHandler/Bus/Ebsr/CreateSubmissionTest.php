<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Bus\Ebsr;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Command\Bus\Ebsr\CreateSubmission as CreateSubmissionCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Bus\Ebsr\CreateSubmission;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use LmcRbacMvc\Service\AuthorizationService;
use LmcRbacMvc\Identity\IdentityInterface;

/**
 * Create Submission Test
 */
class CreateSubmissionTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateSubmission();
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->references = [
            DocumentEntity::class => [
                111 => m::mock(DocumentEntity::class)
            ]
        ];

        $this->refData = [
            EbsrSubmissionEntity::UPLOADED_STATUS,
            EbsrSubmissionEntity::UNKNOWN_SUBMISSION_TYPE,
        ];

        parent::initReferences();
    }

    /**
     * Tests handleCommand
     */
    public function testHandleCommand(): void
    {
        $command = CreateSubmissionCmd::create(
            [
                'document' => 111,
            ]
        );

        $organisation = m::mock(Organisation::class);

        $identity = m::mock(IdentityInterface::class);
        $identity->shouldReceive('getUser->getOrganisationUsers->isEmpty')->once()->andReturn(false);
        $identity->shouldReceive('getUser->getRelatedOrganisation')->once()->andReturn($organisation);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $this->repoMap['EbsrSubmission']->shouldReceive('save')
            ->with(m::type(EbsrSubmissionEntity::class))
            ->once();

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\Command\Result::class, $result);
    }
}
