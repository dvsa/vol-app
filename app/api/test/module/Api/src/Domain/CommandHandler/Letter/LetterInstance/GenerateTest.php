<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstance;

use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance\Generate as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstance as LetterInstanceRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterIssue as LetterIssueRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterType as LetterTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as LetterIssueEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion as LetterIssueVersionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterType as LetterTypeEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\Generate as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Generate LetterInstance Test
 */
class GenerateTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstance', LetterInstanceRepo::class);
        $this->mockRepo('LetterType', LetterTypeRepo::class);
        $this->mockRepo('LetterIssue', LetterIssueRepo::class);
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            LetterInstanceEntity::STATUS_DRAFT,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithLicence(): void
    {
        $letterTypeId = 123;
        $licenceId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'licence' => $licenceId,
            'selectedIssues' => [],
        ]);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getOrganisation')
            ->andReturn($organisation);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        $letterInstance = null;

        $this->repoMap['LetterInstance']->shouldReceive('save')
            ->with(m::type(LetterInstanceEntity::class))
            ->once()
            ->andReturnUsing(
                function (LetterInstanceEntity $entity) use (&$letterInstance) {
                    $letterInstance = $entity;
                    $entity->setId(999);
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(999, $result->getId('letterInstance'));
        $this->assertStringContainsString('Letter instance', $result->getMessages()[0]);
        $this->assertStringContainsString('generated successfully', $result->getMessages()[0]);

        $this->assertNotNull($letterInstance->getReference());
        $this->assertStringStartsWith('LTR', $letterInstance->getReference());
        $this->assertSame($letterType, $letterInstance->getLetterType());
        $this->assertSame($this->refData[LetterInstanceEntity::STATUS_DRAFT], $letterInstance->getStatus());
        $this->assertSame($licence, $letterInstance->getLicence());
        $this->assertSame($organisation, $letterInstance->getOrganisation());
        $this->assertCount(0, $letterInstance->getLetterInstanceIssues());
    }

    public function testHandleCommandWithSelectedIssues(): void
    {
        $letterTypeId = 123;
        $issueId1 = 789;
        $issueId2 = 790;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'selectedIssues' => [$issueId1, $issueId2],
        ]);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

        $issueVersion1 = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $issueVersion1->setId(1001);

        $letterIssue1 = m::mock(LetterIssueEntity::class)->makePartial();
        $letterIssue1->setId($issueId1);
        $letterIssue1->shouldReceive('getCurrentVersion')
            ->andReturn($issueVersion1);

        $this->repoMap['LetterIssue']->shouldReceive('fetchById')
            ->with($issueId1)
            ->once()
            ->andReturn($letterIssue1);

        $issueVersion2 = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $issueVersion2->setId(1002);

        $letterIssue2 = m::mock(LetterIssueEntity::class)->makePartial();
        $letterIssue2->setId($issueId2);
        $letterIssue2->shouldReceive('getCurrentVersion')
            ->andReturn($issueVersion2);

        $this->repoMap['LetterIssue']->shouldReceive('fetchById')
            ->with($issueId2)
            ->once()
            ->andReturn($letterIssue2);

        $letterInstance = null;

        $this->repoMap['LetterInstance']->shouldReceive('save')
            ->with(m::type(LetterInstanceEntity::class))
            ->once()
            ->andReturnUsing(
                function (LetterInstanceEntity $entity) use (&$letterInstance) {
                    $letterInstance = $entity;
                    $entity->setId(999);
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(999, $result->getId('letterInstance'));

        $issues = $letterInstance->getLetterInstanceIssues();
        $this->assertCount(2, $issues);

        $issuesArray = $issues->toArray();

        $this->assertSame($issueVersion1, $issuesArray[0]->getLetterIssueVersion());
        $this->assertSame(0, $issuesArray[0]->getDisplayOrder());
        $this->assertSame($letterInstance, $issuesArray[0]->getLetterInstance());

        $this->assertSame($issueVersion2, $issuesArray[1]->getLetterIssueVersion());
        $this->assertSame(1, $issuesArray[1]->getDisplayOrder());
        $this->assertSame($letterInstance, $issuesArray[1]->getLetterInstance());
    }

    public function testHandleCommandWithApplication(): void
    {
        $letterTypeId = 123;
        $applicationId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'application' => $applicationId,
            'selectedIssues' => [],
        ]);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('getOrganisation')
            ->andReturn($organisation);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId($applicationId);
        $application->shouldReceive('getLicence')
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($application);

        $letterInstance = null;

        $this->repoMap['LetterInstance']->shouldReceive('save')
            ->with(m::type(LetterInstanceEntity::class))
            ->once()
            ->andReturnUsing(
                function (LetterInstanceEntity $entity) use (&$letterInstance) {
                    $letterInstance = $entity;
                    $entity->setId(999);
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(999, $result->getId('letterInstance'));

        $this->assertSame($application, $letterInstance->getApplication());
        $this->assertSame($organisation, $letterInstance->getOrganisation());
    }

    public function testHandleCommandWithCase(): void
    {
        $letterTypeId = 123;
        $caseId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'case' => $caseId,
            'selectedIssues' => [],
        ]);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('getOrganisation')
            ->andReturn($organisation);

        $case = m::mock(CasesEntity::class)->makePartial();
        $case->setId($caseId);
        $case->shouldReceive('getLicence')
            ->andReturn($licence);
        $case->shouldReceive('getApplication')
            ->andReturn(null);

        $this->repoMap['Cases']->shouldReceive('fetchById')
            ->with($caseId)
            ->once()
            ->andReturn($case);

        $letterInstance = null;

        $this->repoMap['LetterInstance']->shouldReceive('save')
            ->with(m::type(LetterInstanceEntity::class))
            ->once()
            ->andReturnUsing(
                function (LetterInstanceEntity $entity) use (&$letterInstance) {
                    $letterInstance = $entity;
                    $entity->setId(999);
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(999, $result->getId('letterInstance'));

        $this->assertSame($case, $letterInstance->getCase());
        $this->assertSame($organisation, $letterInstance->getOrganisation());
    }

    public function testHandleCommandWithCaseViaApplication(): void
    {
        $letterTypeId = 123;
        $caseId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'case' => $caseId,
            'selectedIssues' => [],
        ]);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);
        $licence->shouldReceive('getOrganisation')
            ->andReturn($organisation);

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(222);
        $application->shouldReceive('getLicence')
            ->andReturn($licence);

        $case = m::mock(CasesEntity::class)->makePartial();
        $case->setId($caseId);
        $case->shouldReceive('getLicence')
            ->andReturn(null); // No direct licence
        $case->shouldReceive('getApplication')
            ->andReturn($application);

        $this->repoMap['Cases']->shouldReceive('fetchById')
            ->with($caseId)
            ->once()
            ->andReturn($case);

        $letterInstance = null;

        $this->repoMap['LetterInstance']->shouldReceive('save')
            ->with(m::type(LetterInstanceEntity::class))
            ->once()
            ->andReturnUsing(
                function (LetterInstanceEntity $entity) use (&$letterInstance) {
                    $letterInstance = $entity;
                    $entity->setId(999);
                }
            );

        $result = $this->sut->handleCommand($command);

        $this->assertSame(999, $result->getId('letterInstance'));

        $this->assertSame($case, $letterInstance->getCase());
        $this->assertSame($organisation, $letterInstance->getOrganisation());
    }
}
