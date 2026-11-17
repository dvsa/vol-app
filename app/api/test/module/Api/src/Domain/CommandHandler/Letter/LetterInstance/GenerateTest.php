<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Letter\LetterInstance;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\CommandHandler\Letter\LetterInstance\Generate as CommandHandler;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterAppendix as LetterAppendixRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterChoice as LetterChoiceRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstance as LetterInstanceRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterIssue as LetterIssueRepo;
use Dvsa\Olcs\Api\Domain\Repository\LetterType as LetterTypeRepo;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CasesEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterChoice as LetterChoiceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssue as LetterIssueEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueTodo as LetterIssueTodoEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion as LetterIssueVersionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSection as LetterSectionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVariant as LetterSectionVariantEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion as LetterSectionVersionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTodoVersion as LetterTodoVersionEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterType as LetterTypeEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterTypeSection as LetterTypeSectionEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceComposer;
use Dvsa\Olcs\Api\Service\Letter\LetterInstanceGrabSnapshotter;
use Dvsa\Olcs\Api\Service\Letter\Resolution\VariantResolution;
use Dvsa\Olcs\Api\Service\Letter\SectionVariantResolver;
use Dvsa\Olcs\Transfer\Command\Letter\LetterInstance\Generate as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * Generate LetterInstance Test
 */
final class GenerateTest extends AbstractCommandHandlerTestCase
{
    private m\MockInterface $mockAuthService;

    private m\MockInterface $mockSnapshotter;

    /**
     * Wrap a variant in the resolution the section now hands back.
     *
     * These tests stub the section's resolution seam rather than building whole variant graphs, so
     * they only care which variant was chosen; the surrounding explanation is for the letter type
     * builder's diagnostics and is exercised in SectionVariantResolverTest.
     */
    private function variantResolution(?LetterSectionVariantEntity $chosen): VariantResolution
    {
        return new VariantResolution($chosen, false, 0, [], [], []);
    }

    public function setUp(): void
    {
        // The resolver is pure (no repos, no persistence), so the handler is exercised
        // against the real one — a mock here would let Generate drift from the shared path.
        $this->mockAuthService = m::mock(AuthorizationService::class);
        $this->mockAuthService->shouldReceive('getIdentity')->andReturnNull()->byDefault();

        $this->mockSnapshotter = m::mock(LetterInstanceGrabSnapshotter::class);
        $this->mockSnapshotter->shouldReceive('snapshot')->byDefault();

        $this->mockedSmServices = [
            SectionVariantResolver::class => new SectionVariantResolver(),
            LetterInstanceComposer::class => new LetterInstanceComposer(),
            LetterInstanceGrabSnapshotter::class => $this->mockSnapshotter,
            AuthorizationService::class => $this->mockAuthService,
        ];

        $this->sut = new CommandHandler();
        $this->mockRepo('LetterInstance', LetterInstanceRepo::class);
        $this->mockRepo('LetterType', LetterTypeRepo::class);
        $this->mockRepo('LetterIssue', LetterIssueRepo::class);
        $this->mockRepo('LetterAppendix', LetterAppendixRepo::class);
        $this->mockRepo('LetterChoice', LetterChoiceRepo::class);
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
        $this->assertStringContainsString('Letter instance', (string) $result->getMessages()[0]);
        $this->assertStringContainsString('generated successfully', (string) $result->getMessages()[0]);

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
        $this->assertSame($licence, $letterInstance->getLicence());
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
        $this->assertSame($licence, $letterInstance->getLicence());
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
        $this->assertSame($application, $letterInstance->getApplication());
        $this->assertSame($licence, $letterInstance->getLicence());
        $this->assertSame($organisation, $letterInstance->getOrganisation());
    }

    // ---------------------------------------------------------------
    // Variant resolution tests
    // ---------------------------------------------------------------

    public function testHandleCommandWithMatchingVariantProducesInstanceSections(): void
    {
        $letterTypeId = 123;
        $licenceId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'licence' => $licenceId,
            'selectedIssues' => [],
        ]);

        // Set up licence (for context building: isNi)
        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn(null);
        $licence->shouldReceive('isNi')->andReturn(false);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        // Set up a section version for the default variant
        $sectionVersion = m::mock(LetterSectionVersionEntity::class)->makePartial();
        $sectionVersion->setId(500);

        // Create a LetterSection with a default variant (all null conditions)
        $defaultVariant = m::mock(LetterSectionVariantEntity::class)->makePartial();
        $defaultVariant->shouldReceive('isDefault')->andReturn(true);
        $defaultVariant->shouldReceive('matchesContext')->andReturn(true);
        $defaultVariant->shouldReceive('getCurrentVersion')->andReturn($sectionVersion);

        $section = m::mock(LetterSectionEntity::class)->makePartial();
        $section->shouldReceive('explainVariantForContext')->andReturn($this->variantResolution($defaultVariant));

        // Create the letter type section
        $typeSection = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);
        $typeSection->shouldReceive('getIsRequired')->andReturn(false);
        $typeSection->shouldReceive('getDisplayOrder')->andReturn(1);

        // Create the letter type with sections
        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);
        $letterType->shouldReceive('getLetterTypeSections')
            ->andReturn(new ArrayCollection([$typeSection]));

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

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

        // Should have one instance section
        $instanceSections = $letterInstance->getLetterInstanceSections();
        $this->assertCount(1, $instanceSections);

        $instanceSection = $instanceSections->first();
        $this->assertSame($sectionVersion, $instanceSection->getLetterSectionVersion());
        $this->assertSame(1, $instanceSection->getDisplayOrder());
        $this->assertSame($letterInstance, $instanceSection->getLetterInstance());
    }

    public function testHandleCommandWithNoMatchingVariantSkipsSection(): void
    {
        $letterTypeId = 123;
        $licenceId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'licence' => $licenceId,
            'selectedIssues' => [],
        ]);

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn(null);
        $licence->shouldReceive('isNi')->andReturn(false);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        // Section where getVariantForContext returns null (no matching variant)
        $section = m::mock(LetterSectionEntity::class)->makePartial();
        $section->shouldReceive('explainVariantForContext')->andReturn($this->variantResolution(null));

        $typeSection = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);
        $typeSection->shouldReceive('getIsRequired')->andReturn(false);
        $typeSection->shouldReceive('getDisplayOrder')->andReturn(1);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);
        $letterType->shouldReceive('getLetterTypeSections')
            ->andReturn(new ArrayCollection([$typeSection]));

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

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

        // Section was skipped, so no instance sections
        $this->assertCount(0, $letterInstance->getLetterInstanceSections());
    }

    public function testHandleCommandWithRequiredSectionSkippedAddsWarning(): void
    {
        $letterTypeId = 123;
        $licenceId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'licence' => $licenceId,
        ]);

        $licence = m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn(null);
        $licence->shouldReceive('isNi')->andReturn(false);
        $licence->shouldReceive('getOrganisation')->andReturn(null);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        // Section where getVariantForContext returns null AND section is required
        $section = m::mock(LetterSectionEntity::class)->makePartial();
        $section->shouldReceive('explainVariantForContext')->andReturn($this->variantResolution(null));
        $section->shouldReceive('getName')->andReturn('Introductory wording');
        $section->shouldReceive('getSectionKey')->andReturn('intro_wording');

        $typeSection = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);
        $typeSection->shouldReceive('getIsRequired')->andReturn(true);
        $typeSection->shouldReceive('getDisplayOrder')->andReturn(1);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);
        $letterType->shouldReceive('getLetterTypeSections')
            ->andReturn(new ArrayCollection([$typeSection]));

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

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

        // Letter still created successfully
        $this->assertSame(999, $result->getId('letterInstance'));

        // Section was skipped
        $this->assertCount(0, $letterInstance->getLetterInstanceSections());

        // But a warning was added
        $this->assertTrue($result->getFlag('hasRequiredSectionWarnings'));
        $messages = $result->getMessages();
        $warningFound = false;
        foreach ($messages as $msg) {
            if (str_contains((string) $msg, 'Required section "Introductory wording"')) {
                $warningFound = true;
                break;
            }
        }
        $this->assertTrue($warningFound, 'Expected a warning message about the required section');
    }

    public function testHandleCommandWithVariantNoCurrentVersionSkipsSection(): void
    {
        $letterTypeId = 123;
        $licenceId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'licence' => $licenceId,
            'selectedIssues' => [],
        ]);

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn(null);
        $licence->shouldReceive('isNi')->andReturn(false);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        // Variant matches but has no current version
        $variant = m::mock(LetterSectionVariantEntity::class)->makePartial();
        $variant->shouldReceive('getCurrentVersion')->andReturn(null);

        $section = m::mock(LetterSectionEntity::class)->makePartial();
        $section->shouldReceive('explainVariantForContext')->andReturn($this->variantResolution($variant));

        $typeSection = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);
        $typeSection->shouldReceive('getIsRequired')->andReturn(false);
        $typeSection->shouldReceive('getDisplayOrder')->andReturn(1);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);
        $letterType->shouldReceive('getLetterTypeSections')
            ->andReturn(new ArrayCollection([$typeSection]));

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

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

        // Section skipped because variant had no current version
        $this->assertCount(0, $letterInstance->getLetterInstanceSections());
    }

    public function testHandleCommandRecordsSelectedChoicesAsLetterInstanceChoices(): void
    {
        $letterTypeId = 123;
        $choiceId1 = 50;
        $choiceId2 = 51;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'selectedIssues' => [],
            'selectedChoices' => [$choiceId1, $choiceId2],
        ]);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

        $letterChoice1 = m::mock(LetterChoiceEntity::class)->makePartial();
        $letterChoice1->setId($choiceId1);

        $letterChoice2 = m::mock(LetterChoiceEntity::class)->makePartial();
        $letterChoice2->setId($choiceId2);

        $this->repoMap['LetterChoice']->shouldReceive('fetchById')
            ->with($choiceId1)
            ->once()
            ->andReturn($letterChoice1);

        $this->repoMap['LetterChoice']->shouldReceive('fetchById')
            ->with($choiceId2)
            ->once()
            ->andReturn($letterChoice2);

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

        $choices = $letterInstance->getLetterInstanceChoices();
        $this->assertCount(2, $choices);

        $choicesArray = $choices->toArray();
        $this->assertSame($letterChoice1, $choicesArray[0]->getLetterChoice());
        $this->assertSame($letterInstance, $choicesArray[0]->getLetterInstance());
        $this->assertSame($letterChoice2, $choicesArray[1]->getLetterChoice());
        $this->assertSame($letterInstance, $choicesArray[1]->getLetterInstance());
    }

    public function testHandleCommandBuildsContextFromApplicationAndLicence(): void
    {
        $letterTypeId = 123;
        $applicationId = 456;
        $licenceId = 789;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'application' => $applicationId,
            'selectedIssues' => [],
            'selectedChoices' => [10, 20],
        ]);

        // Set up licence with GoodsOrPsv and isNi
        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId('lcat_gv');

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(100);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn($goodsOrPsv);
        $licence->shouldReceive('isNi')->andReturn(true);

        // Set up application with isVariation and goodsOrPsv
        $appGoodsOrPsv = m::mock(RefData::class)->makePartial();
        $appGoodsOrPsv->setId('lcat_gv');

        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId($applicationId);
        $application->shouldReceive('getLicence')->andReturn($licence);
        $application->shouldReceive('getGoodsOrPsv')->andReturn($appGoodsOrPsv);
        $application->shouldReceive('getIsVariation')->andReturn(1);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with($applicationId)
            ->once()
            ->andReturn($application);

        // Set up section version for a GV+Variation+NI variant
        $sectionVersion = m::mock(LetterSectionVersionEntity::class)->makePartial();
        $sectionVersion->setId(600);

        // Section with a conditioned variant that requires GV + Variation + NI + choice 10
        $section = m::mock(LetterSectionEntity::class)->makePartial();
        $section->shouldReceive('explainVariantForContext')
            ->with(m::on(
                // Verify the context was built correctly from application and licence
                fn($context) => $context['goodsOrPsv'] === 'lcat_gv'
                && $context['isVariation'] === true
                && $context['isNi'] === true
                && $context['selectedChoiceIds'] === [10, 20]
            ))
            ->andReturnUsing(function () use ($sectionVersion) {
                $variant = m::mock(LetterSectionVariantEntity::class)->makePartial();
                $variant->shouldReceive('getCurrentVersion')->andReturn($sectionVersion);
                return $this->variantResolution($variant);
            });

        $typeSection = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);
        $typeSection->shouldReceive('getDisplayOrder')->andReturn(0);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);
        $letterType->shouldReceive('getLetterTypeSections')
            ->andReturn(new ArrayCollection([$typeSection]));

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

        // Mock choice repo for selectedChoices
        $letterChoice1 = m::mock(LetterChoiceEntity::class)->makePartial();
        $letterChoice1->setId(10);

        $letterChoice2 = m::mock(LetterChoiceEntity::class)->makePartial();
        $letterChoice2->setId(20);

        $this->repoMap['LetterChoice']->shouldReceive('fetchById')
            ->with(10)
            ->once()
            ->andReturn($letterChoice1);

        $this->repoMap['LetterChoice']->shouldReceive('fetchById')
            ->with(20)
            ->once()
            ->andReturn($letterChoice2);

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

        // Verify the section was included (variant matched the context)
        $this->assertCount(1, $letterInstance->getLetterInstanceSections());
        $instanceSection = $letterInstance->getLetterInstanceSections()->first();
        $this->assertSame($sectionVersion, $instanceSection->getLetterSectionVersion());

        // Verify choices were recorded
        $this->assertCount(2, $letterInstance->getLetterInstanceChoices());
    }

    public function testHandleCommandMultipleSectionsMixedVariantMatching(): void
    {
        $letterTypeId = 123;
        $licenceId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'licence' => $licenceId,
            'selectedIssues' => [],
        ]);

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn(null);
        $licence->shouldReceive('isNi')->andReturn(false);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        // Section 1: has matching variant with version
        $version1 = m::mock(LetterSectionVersionEntity::class)->makePartial();
        $version1->setId(501);

        $variant1 = m::mock(LetterSectionVariantEntity::class)->makePartial();
        $variant1->shouldReceive('getCurrentVersion')->andReturn($version1);

        $section1 = m::mock(LetterSectionEntity::class)->makePartial();
        $section1->shouldReceive('explainVariantForContext')->andReturn($this->variantResolution($variant1));

        $typeSection1 = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection1->shouldReceive('getLetterSection')->andReturn($section1);
        $typeSection1->shouldReceive('getIsRequired')->andReturn(false);
        $typeSection1->shouldReceive('getDisplayOrder')->andReturn(0);

        // Section 2: no matching variant (getVariantForContext returns null)
        $section2 = m::mock(LetterSectionEntity::class)->makePartial();
        $section2->shouldReceive('explainVariantForContext')->andReturn($this->variantResolution(null));

        $typeSection2 = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection2->shouldReceive('getLetterSection')->andReturn($section2);
        $typeSection2->shouldReceive('getIsRequired')->andReturn(false);
        $typeSection2->shouldReceive('getDisplayOrder')->andReturn(1);

        // Section 3: has matching variant with version
        $version3 = m::mock(LetterSectionVersionEntity::class)->makePartial();
        $version3->setId(503);

        $variant3 = m::mock(LetterSectionVariantEntity::class)->makePartial();
        $variant3->shouldReceive('getCurrentVersion')->andReturn($version3);

        $section3 = m::mock(LetterSectionEntity::class)->makePartial();
        $section3->shouldReceive('explainVariantForContext')->andReturn($this->variantResolution($variant3));

        $typeSection3 = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection3->shouldReceive('getLetterSection')->andReturn($section3);
        $typeSection3->shouldReceive('getIsRequired')->andReturn(false);
        $typeSection3->shouldReceive('getDisplayOrder')->andReturn(2);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);
        $letterType->shouldReceive('getLetterTypeSections')
            ->andReturn(new ArrayCollection([$typeSection1, $typeSection2, $typeSection3]));

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

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

        // 2 of 3 sections matched, 1 was skipped
        $instanceSections = $letterInstance->getLetterInstanceSections();
        $this->assertCount(2, $instanceSections);

        $sectionsArray = $instanceSections->toArray();
        $this->assertSame($version1, $sectionsArray[0]->getLetterSectionVersion());
        $this->assertSame(0, $sectionsArray[0]->getDisplayOrder());
        $this->assertSame($version3, $sectionsArray[1]->getLetterSectionVersion());
        $this->assertSame(2, $sectionsArray[1]->getDisplayOrder());
    }

    public function testHandleCommandContextBuiltFromLicenceWhenNoApplication(): void
    {
        $letterTypeId = 123;
        $licenceId = 456;

        $command = Cmd::create([
            'letterType' => $letterTypeId,
            'licence' => $licenceId,
            'selectedIssues' => [],
        ]);

        $goodsOrPsv = m::mock(RefData::class)->makePartial();
        $goodsOrPsv->setId('lcat_psv');

        $organisation = m::mock(OrganisationEntity::class)->makePartial();
        $organisation->setId(789);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getOrganisation')->andReturn($organisation);
        $licence->shouldReceive('getGoodsOrPsv')->andReturn($goodsOrPsv);
        $licence->shouldReceive('isNi')->andReturn(true);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        // Set up section that checks the context
        $sectionVersion = m::mock(LetterSectionVersionEntity::class)->makePartial();
        $sectionVersion->setId(700);

        $section = m::mock(LetterSectionEntity::class)->makePartial();
        $section->shouldReceive('explainVariantForContext')
            ->with(m::on(
                // When no application, goodsOrPsv comes from licence
                // isVariation is null (no application)
                // isNi comes from licence
                fn($context) => $context['goodsOrPsv'] === 'lcat_psv'
                && $context['isVariation'] === null
                && $context['isNi'] === true
                && $context['selectedChoiceIds'] === []
            ))
            ->andReturnUsing(function () use ($sectionVersion) {
                $variant = m::mock(LetterSectionVariantEntity::class)->makePartial();
                $variant->shouldReceive('getCurrentVersion')->andReturn($sectionVersion);
                return $this->variantResolution($variant);
            });

        $typeSection = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);
        $typeSection->shouldReceive('getDisplayOrder')->andReturn(0);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);
        $letterType->shouldReceive('getLetterTypeSections')
            ->andReturn(new ArrayCollection([$typeSection]));

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

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
        $this->assertCount(1, $letterInstance->getLetterInstanceSections());
    }

    private function refData(string $id): RefData
    {
        $refData = m::mock(RefData::class)->makePartial();
        $refData->setId($id);

        return $refData;
    }

    private function expectLetterTypeAndLicence(int $letterTypeId, int $licenceId, string $goodsOrPsv): m\MockInterface
    {
        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);

        $this->repoMap['LetterType']->shouldReceive('fetchById')
            ->with($letterTypeId)
            ->once()
            ->andReturn($letterType);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId($licenceId);
        $licence->shouldReceive('getOrganisation')->andReturnNull();
        $licence->shouldReceive('getGoodsOrPsv')->andReturn($this->refData($goodsOrPsv));
        $licence->shouldReceive('isNi')->andReturn(false);

        $this->repoMap['Licence']->shouldReceive('fetchById')
            ->with($licenceId)
            ->once()
            ->andReturn($licence);

        return $letterType;
    }

    private function captureSavedInstance(?LetterInstanceEntity &$letterInstance): void
    {
        $this->repoMap['LetterInstance']->shouldReceive('save')
            ->with(m::type(LetterInstanceEntity::class))
            ->once()
            ->andReturnUsing(
                function (LetterInstanceEntity $entity) use (&$letterInstance) {
                    $letterInstance = $entity;
                    $entity->setId(999);
                }
            );
    }

    public function testHandleCommandSkipsIssuesForTheOtherLicenceType(): void
    {
        $command = Cmd::create([
            'letterType' => 123,
            'licence' => 456,
            'selectedIssues' => [1, 2],
        ]);

        $this->expectLetterTypeAndLicence(123, 456, 'lcat_psv');

        $goodsOnlyVersion = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $goodsOnlyVersion->setGoodsOrPsv($this->refData('lcat_gv'));

        $anyVersion = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $anyVersion->setGoodsOrPsv(null);

        foreach ([1 => $goodsOnlyVersion, 2 => $anyVersion] as $issueId => $version) {
            $issue = m::mock(LetterIssueEntity::class)->makePartial();
            $issue->shouldReceive('getCurrentVersion')->andReturn($version);

            $this->repoMap['LetterIssue']->shouldReceive('fetchById')
                ->with($issueId)
                ->once()
                ->andReturn($issue);
        }

        $letterInstance = null;
        $this->captureSavedInstance($letterInstance);

        $this->sut->handleCommand($command);

        $issues = $letterInstance->getLetterInstanceIssues();
        $this->assertCount(1, $issues);
        $this->assertSame($anyVersion, $issues->first()->getLetterIssueVersion());
    }

    public function testHandleCommandIgnoresAnIssuePostedTwice(): void
    {
        $command = Cmd::create([
            'letterType' => 123,
            'licence' => 456,
            'selectedIssues' => [1, '1'],
        ]);

        $this->expectLetterTypeAndLicence(123, 456, 'lcat_psv');

        $version = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $version->setGoodsOrPsv(null);
        $issue = m::mock(LetterIssueEntity::class)->makePartial();
        $issue->shouldReceive('getCurrentVersion')->andReturn($version);
        $this->repoMap['LetterIssue']->shouldReceive('fetchById')->once()->andReturn($issue);

        $letterInstance = null;
        $this->captureSavedInstance($letterInstance);

        $this->sut->handleCommand($command);

        $this->assertCount(1, $letterInstance->getLetterInstanceIssues());
    }

    public function testHandleCommandDropsChoicesForTheOtherLicenceType(): void
    {
        $command = Cmd::create([
            'letterType' => 123,
            'licence' => 456,
            'selectedIssues' => [],
            'selectedChoices' => [1, 2],
        ]);

        $letterType = $this->expectLetterTypeAndLicence(123, 456, 'lcat_psv');

        $goodsOnlyChoice = m::mock(LetterChoiceEntity::class)->makePartial();
        $goodsOnlyChoice->setId(1);
        $goodsOnlyChoice->setGoodsOrPsv($this->refData('lcat_gv'));

        $anyChoice = m::mock(LetterChoiceEntity::class)->makePartial();
        $anyChoice->setId(2);

        $this->repoMap['LetterChoice']->shouldReceive('fetchById')->with(1)->once()->andReturn($goodsOnlyChoice);
        $this->repoMap['LetterChoice']->shouldReceive('fetchById')->with(2)->once()->andReturn($anyChoice);

        // A dropped choice must not steer section variants either
        $section = m::mock(LetterSectionEntity::class)->makePartial();
        $section->shouldReceive('explainVariantForContext')
            ->with(m::on(fn($context) => $context['selectedChoiceIds'] === [2]))
            ->once()
            ->andReturn($this->variantResolution(null));

        $typeSection = m::mock(LetterTypeSectionEntity::class)->makePartial();
        $typeSection->shouldReceive('getLetterSection')->andReturn($section);
        $typeSection->shouldReceive('getDisplayOrder')->andReturn(0);

        $letterType->shouldReceive('getLetterTypeSections')
            ->andReturn(new ArrayCollection([$typeSection]));

        $letterInstance = null;
        $this->captureSavedInstance($letterInstance);

        $this->sut->handleCommand($command);

        $choices = $letterInstance->getLetterInstanceChoices();
        $this->assertCount(1, $choices);
        $this->assertSame($anyChoice, $choices->first()->getLetterChoice());
    }

    /**
     * VOL-7280: when two selected issues link to the same LetterTodoVersion, exactly one
     * LetterInstanceTodo should be created and attached to the FIRST issue in display order.
     */
    public function testHandleCommandDedupesIssueTodosAcrossSelectedIssues(): void
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

        // A single LetterTodoVersion is linked to both issues -> should dedupe to one
        $sharedTodoVersion = m::mock(LetterTodoVersionEntity::class)->makePartial();
        $sharedTodoVersion->setId(5000);

        $junction1 = m::mock(LetterIssueTodoEntity::class)->makePartial();
        $junction1->shouldReceive('getLetterTodoVersion')->andReturn($sharedTodoVersion);

        $issueVersion1 = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $issueVersion1->setId(1001);
        $issueVersion1->shouldReceive('getLetterIssueTodos')
            ->andReturn(new ArrayCollection([$junction1]));

        $letterIssue1 = m::mock(LetterIssueEntity::class)->makePartial();
        $letterIssue1->setId($issueId1);
        $letterIssue1->shouldReceive('getCurrentVersion')->andReturn($issueVersion1);

        $this->repoMap['LetterIssue']->shouldReceive('fetchById')
            ->with($issueId1)
            ->once()
            ->andReturn($letterIssue1);

        $junction2 = m::mock(LetterIssueTodoEntity::class)->makePartial();
        $junction2->shouldReceive('getLetterTodoVersion')->andReturn($sharedTodoVersion);

        $issueVersion2 = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $issueVersion2->setId(1002);
        $issueVersion2->shouldReceive('getLetterIssueTodos')
            ->andReturn(new ArrayCollection([$junction2]));

        $letterIssue2 = m::mock(LetterIssueEntity::class)->makePartial();
        $letterIssue2->setId($issueId2);
        $letterIssue2->shouldReceive('getCurrentVersion')->andReturn($issueVersion2);

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

        $this->sut->handleCommand($command);

        $todos = $letterInstance->getLetterInstanceTodos();
        $this->assertCount(1, $todos, 'Same to-do on two issues should be deduped to one');

        $instanceTodo = $todos->first();
        $this->assertSame($sharedTodoVersion, $instanceTodo->getLetterTodoVersion());
        $this->assertSame(
            $letterInstance->getLetterInstanceIssues()->first(),
            $instanceTodo->getLetterInstanceIssue(),
            'Deduped to-do should attach to the first issue in display order'
        );
        $this->assertSame($letterInstance, $instanceTodo->getLetterInstance());
    }

    public function testHandleCommandStampsCurrentUserAndSnapshotsGrabsBeforeSaving(): void
    {
        $user = m::mock(UserEntity::class);
        $user->shouldReceive('isAnonymous')->andReturn(false);
        $identity = m::mock();
        $identity->shouldReceive('getUser')->andReturn($user);
        $this->mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId(123);
        $this->repoMap['LetterType']->shouldReceive('fetchById')->with(123)->andReturn($letterType);

        $order = [];

        $this->mockSnapshotter->shouldReceive('snapshot')
            ->once()
            ->with(m::type(LetterInstanceEntity::class))
            ->andReturnUsing(function (LetterInstanceEntity $entity) use (&$order, $user) {
                $order[] = 'snapshot';
                $this->assertSame($user, $entity->getCreatedBy(), 'createdBy must be set before grabs resolve');
            });

        $this->repoMap['LetterInstance']->shouldReceive('save')
            ->once()
            ->andReturnUsing(function (LetterInstanceEntity $entity) use (&$order) {
                $order[] = 'save';
                $entity->setId(999);
            });

        $this->sut->handleCommand(Cmd::create(['letterType' => 123, 'selectedIssues' => []]));

        $this->assertSame(['snapshot', 'save'], $order);
    }

    public function testHandleCommandDoesNotStampAnAnonymousUser(): void
    {
        // same rule as OlcsBlameableListener: anon is transient and must never be persisted
        $identity = m::mock();
        $identity->shouldReceive('getUser')->andReturn(UserEntity::anon());
        $this->mockAuthService->shouldReceive('getIdentity')->andReturn($identity);

        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId(123);
        $this->repoMap['LetterType']->shouldReceive('fetchById')->with(123)->andReturn($letterType);

        $saved = null;
        $this->repoMap['LetterInstance']->shouldReceive('save')
            ->once()
            ->andReturnUsing(function (LetterInstanceEntity $entity) use (&$saved) {
                $saved = $entity;
                $entity->setId(999);
            });

        $this->sut->handleCommand(Cmd::create(['letterType' => 123, 'selectedIssues' => []]));

        $this->assertNull($saved->getCreatedBy());
    }

    private function paragraph(string $text): array
    {
        return ['blocks' => [['type' => 'paragraph', 'data' => ['text' => $text]]]];
    }

    /**
     * A letter covering library issue 789, with that issue's wording changed by the caseworker
     *
     * @param array<string, int> $links what it is linked to, by the name of the link
     */
    private function letterCoveringIssue789(string $status, int $letterTypeId, array $links, bool $reworded = true): LetterInstanceEntity
    {
        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId($letterTypeId);

        $letter = new LetterInstanceEntity();
        $letter->setStatus(new RefData($status));
        $letter->setLetterType($letterType);
        foreach ($links as $link => $id) {
            $linked = m::mock(['licence' => LicenceEntity::class, 'application' => ApplicationEntity::class][$link])->makePartial();
            $linked->setId($id);
            $letter->{'set' . ucfirst($link)}($linked);
        }

        $libraryIssue = m::mock(LetterIssueEntity::class)->makePartial();
        $libraryIssue->setId(789);
        $version = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $version->setLetterIssue($libraryIssue);
        $version->setDefaultBodyContent($this->paragraph('Library wording'));

        $issue = new \Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue();
        $issue->setLetterIssueVersion($version);
        $issue->setDisplayOrder(0);
        // saved either way: an editor stores its text whether or not anything was typed
        $issue->setEditedContentFromArray($this->paragraph($reworded ? 'Reworded by the caseworker' : 'Library wording'));
        $letter->addLetterInstanceIssue($issue);

        return $letter;
    }

    /**
     * Library issue 789 as it is now, on a newer version than the sent letter used
     */
    private function expectIssue789(string $heading = 'No financial evidence'): void
    {
        $libraryIssue = m::mock(LetterIssueEntity::class)->makePartial();
        $libraryIssue->setId(789);
        $version = m::mock(LetterIssueVersionEntity::class)->makePartial();
        $version->setLetterIssue($libraryIssue);
        $version->setGoodsOrPsv(null);
        $version->setHeading($heading);
        $libraryIssue->shouldReceive('getCurrentVersion')->andReturn($version);

        $this->repoMap['LetterIssue']->shouldReceive('fetchById')->with(789)->andReturn($libraryIssue);
    }

    public function testStartingFromASentLetterBringsOverItsChangedWording(): void
    {
        $command = Cmd::create([
            'letterType' => 123,
            'licence' => 456,
            'selectedIssues' => [789],
            'startFromLetter' => 55,
        ]);

        $this->expectLetterTypeAndLicence(123, 456, 'lcat_psv');
        $this->expectIssue789();
        $this->repoMap['LetterInstance']->shouldReceive('fetchById')->with(55)->once()->andReturn(
            $this->letterCoveringIssue789(LetterInstanceEntity::STATUS_SENT, 123, ['licence' => 456])
        );

        $letterInstance = null;
        $this->captureSavedInstance($letterInstance);

        $result = $this->sut->handleCommand($command);

        $this->assertSame(
            $this->paragraph('Reworded by the caseworker'),
            $letterInstance->getLetterInstanceIssues()->first()->getEditedContent()
        );
        // copied wording has the sent letter's dates and details in it, so the answer names the
        // issues for the caseworker to check
        $this->assertSame(['No financial evidence'], $result->getFlag('wordingCopiedTo'));
    }

    /**
     * Starts a letter on licence 456 from sent letter 55 and hands back the answer
     */
    private function startFromSentLetter(LetterInstanceEntity $sentLetter): \Dvsa\Olcs\Api\Domain\Command\Result
    {
        $this->expectLetterTypeAndLicence(123, 456, 'lcat_psv');
        $this->repoMap['LetterInstance']->shouldReceive('fetchById')->with(55)->once()->andReturn($sentLetter);

        $letterInstance = null;
        $this->captureSavedInstance($letterInstance);

        return $this->sut->handleCommand(Cmd::create([
            'letterType' => 123,
            'licence' => 456,
            'selectedIssues' => [789],
            'startFromLetter' => 55,
        ]));
    }

    public function testNothingIsNamedWhenTheSentLetterHadNoChangedWording(): void
    {
        $this->expectIssue789();

        $result = $this->startFromSentLetter(
            $this->letterCoveringIssue789(LetterInstanceEntity::STATUS_SENT, 123, ['licence' => 456], false)
        );

        $this->assertNull($result->getFlag('wordingCopiedTo'));
    }

    public function testAnIssueWithNoHeadingIsStillNamed(): void
    {
        $this->expectIssue789('');

        $result = $this->startFromSentLetter(
            $this->letterCoveringIssue789(LetterInstanceEntity::STATUS_SENT, 123, ['licence' => 456])
        );

        $this->assertSame(['Issue'], $result->getFlag('wordingCopiedTo'));
    }

    /**
     * The form doesn't offer a letter that isn't there, so it is refused the same way as any
     * other letter the form didn't offer, in words the dialog can show
     */
    public function testALetterThatDoesNotExistCannotBeStartedFrom(): void
    {
        $this->expectLetterTypeAndLicence(123, 456, 'lcat_psv');
        $this->expectIssue789();
        $this->repoMap['LetterInstance']->shouldReceive('fetchById')->with(55)->andThrow(
            new \Dvsa\Olcs\Api\Domain\Exception\NotFoundException('Resource not found')
        );
        $this->repoMap['LetterInstance']->shouldReceive('save')->never();

        try {
            $this->sut->handleCommand(Cmd::create([
                'letterType' => 123,
                'licence' => 456,
                'selectedIssues' => [789],
                'startFromLetter' => 55,
            ]));
            $this->fail('The letter was created');
        } catch (\Dvsa\Olcs\Api\Domain\Exception\ValidationException $e) {
            $this->assertSame(['The letter you picked to start from cannot be used for this letter'], $e->getMessages());
        }
    }

    /**
     * The API's filters hand over an empty value, or one that isn't a number, as an empty
     * string. Neither is a letter, so the new letter is made as if none was picked.
     */
    public function testAnEmptyLetterToStartFromIsNoLetter(): void
    {
        $command = Cmd::create([
            'letterType' => 123,
            'licence' => 456,
            'selectedIssues' => [789],
            'startFromLetter' => '',
        ]);

        $this->expectLetterTypeAndLicence(123, 456, 'lcat_psv');
        $this->expectIssue789();
        $this->repoMap['LetterInstance']->shouldReceive('fetchById')->never();

        $letterInstance = null;
        $this->captureSavedInstance($letterInstance);

        $result = $this->sut->handleCommand($command);

        $this->assertNull($letterInstance->getLetterInstanceIssues()->first()->getEditedContent());
        $this->assertNull($result->getFlag('wordingCopiedTo'));
    }

    /**
     * The form only offers letters of the same type that were sent for whatever the new letter
     * is raised on. Any other id didn't come from the form, and its wording isn't handed out.
     *
     * @param array<string, int> $links
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('lettersTheFormDoesNotOffer')]
    public function testALetterTheFormDidNotOfferCannotBeStartedFrom(string $status, int $letterTypeId, array $links): void
    {
        $command = Cmd::create([
            'letterType' => 123,
            'licence' => 456,
            'selectedIssues' => [789],
            'startFromLetter' => 55,
        ]);

        $this->expectLetterTypeAndLicence(123, 456, 'lcat_psv');
        $this->expectIssue789();
        $this->repoMap['LetterInstance']->shouldReceive('fetchById')->with(55)->andReturn(
            $this->letterCoveringIssue789($status, $letterTypeId, $links)
        );
        $this->repoMap['LetterInstance']->shouldReceive('save')->never();

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleCommand($command);
    }

    /**
     * A letter linked to nothing was raised on nothing, and that isn't the same record as
     * another letter linked to nothing
     */
    public function testALetterRaisedOnNothingCannotBeStartedFrom(): void
    {
        $letterType = m::mock(LetterTypeEntity::class)->makePartial();
        $letterType->setId(123);
        $this->repoMap['LetterType']->shouldReceive('fetchById')->with(123)->andReturn($letterType);
        $this->expectIssue789();
        $this->repoMap['LetterInstance']->shouldReceive('fetchById')->with(55)->andReturn(
            $this->letterCoveringIssue789(LetterInstanceEntity::STATUS_SENT, 123, [])
        );
        $this->repoMap['LetterInstance']->shouldReceive('save')->never();

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleCommand(Cmd::create(['letterType' => 123, 'selectedIssues' => [789], 'startFromLetter' => 55]));
    }

    public static function lettersTheFormDoesNotOffer(): array
    {
        return [
            'not sent yet' => [LetterInstanceEntity::STATUS_DRAFT, 123, ['licence' => 456]],
            'another type of letter' => [LetterInstanceEntity::STATUS_SENT, 124, ['licence' => 456]],
            'sent for another licence' => [LetterInstanceEntity::STATUS_SENT, 123, ['licence' => 457]],
            // linked to the licence, but raised on one of its applications
            'sent for an application on the licence' => [
                LetterInstanceEntity::STATUS_SENT,
                123,
                ['licence' => 456, 'application' => 21],
            ],
        ];
    }
}
