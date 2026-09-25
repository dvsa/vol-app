<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\ApplicationCompletion;

use Dvsa\Olcs\Api\Domain\Command\ApplicationCompletion\UpdateKnowledgeExperienceStatus as Cmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\ApplicationCompletion\UpdateKnowledgeExperienceStatus;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationCompletion as ApplicationCompletionEntity;

#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
final class UpdateKnowledgeExperienceStatusTest extends AbstractUpdateStatusTestCase
{
    protected $section = 'KnowledgeExperience';

    public function setUp(): void
    {
        $this->sut = new UpdateKnowledgeExperienceStatus();
        $this->command = Cmd::create(['id' => 111]);

        parent::setUp();
    }

    public function testHandleCommandIncompleteWhenNothingSelected(): void
    {
        $this->applicationCompletion
            ->setKnowledgeExperienceStatus(
                ApplicationCompletionEntity::STATUS_NOT_STARTED
            );

        $this->application
            ->setKnowledgeExperienceEvidenceUploaded(null);

        $this->application
            ->setKnowledgeExperienceOlat(null);

        $this->expectStatusChange(
            ApplicationCompletionEntity::STATUS_INCOMPLETE
        );
    }

    public function testHandleCommandCompleteWhenEvidenceUploaded(): void
    {
        $this->applicationCompletion
            ->setKnowledgeExperienceStatus(
                ApplicationCompletionEntity::STATUS_NOT_STARTED
            );

        $this->application
            ->setKnowledgeExperienceEvidenceUploaded(
                ApplicationEntity::FINANCIAL_EVIDENCE_UPLOADED
            );

        $this->application
            ->setKnowledgeExperienceOlat('N');

        $this->expectStatusChange(
            ApplicationCompletionEntity::STATUS_COMPLETE
        );
    }

    public function testHandleCommandIncompleteWhenUploadLater(): void
    {
        $this->applicationCompletion
            ->setKnowledgeExperienceStatus(
                ApplicationCompletionEntity::STATUS_NOT_STARTED
            );

        $this->application
            ->setKnowledgeExperienceEvidenceUploaded(
                ApplicationEntity::FINANCIAL_EVIDENCE_UPLOAD_LATER
            );

        $this->application
            ->setKnowledgeExperienceOlat('N');

        $this->expectStatusChange(
            ApplicationCompletionEntity::STATUS_INCOMPLETE
        );
    }

    public function testHandleCommandCompleteWhenOlatSelected(): void
    {
        $this->applicationCompletion
            ->setKnowledgeExperienceStatus(
                ApplicationCompletionEntity::STATUS_NOT_STARTED
            );

        $this->application
            ->setKnowledgeExperienceEvidenceUploaded(null);

        $this->application
            ->setKnowledgeExperienceOlat('Y');

        $this->expectStatusChange(
            ApplicationCompletionEntity::STATUS_COMPLETE
        );
    }

    public function testHandleCommandWithoutChange(): void
    {
        $this->applicationCompletion
            ->setKnowledgeExperienceStatus(
                ApplicationCompletionEntity::STATUS_COMPLETE
            );

        $this->application
            ->setKnowledgeExperienceEvidenceUploaded(null);

        $this->application
            ->setKnowledgeExperienceOlat('Y');

        $this->expectStatusUnchanged(
            ApplicationCompletionEntity::STATUS_COMPLETE
        );
    }
}
