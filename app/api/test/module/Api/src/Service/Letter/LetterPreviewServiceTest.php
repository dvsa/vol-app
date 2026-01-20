<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Letter;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\ContactDetails\ContactDetails;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueType;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Person\Person;
use Dvsa\Olcs\Api\Entity\User\User;
use Dvsa\Olcs\Api\Service\Letter\LetterPreviewService;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererInterface;
use Dvsa\Olcs\Api\Service\Letter\SectionRenderer\SectionRendererPluginManager;
use Dvsa\Olcs\Api\Service\Letter\VolGrabReplacementService;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * @covers \Dvsa\Olcs\Api\Service\Letter\LetterPreviewService
 */
class LetterPreviewServiceTest extends MockeryTestCase
{
    private LetterPreviewService $sut;
    private m\MockInterface|SectionRendererPluginManager $mockRendererManager;
    private m\MockInterface $mockContentStore;
    private m\MockInterface $mockDocTemplateRepo;
    private m\MockInterface|VolGrabReplacementService $mockVolGrabReplacementService;

    public function setUp(): void
    {
        $this->mockRendererManager = m::mock(SectionRendererPluginManager::class);
        $this->mockContentStore = m::mock();
        $this->mockDocTemplateRepo = m::mock();
        $this->mockVolGrabReplacementService = m::mock(VolGrabReplacementService::class);

        // Default: logo lookup returns empty (no logo found)
        $this->mockDocTemplateRepo->shouldReceive('fetchByTemplateSlug')
            ->with('otclogo-letters')
            ->andReturn(null)
            ->byDefault();

        $this->mockVolGrabReplacementService->shouldReceive('replaceGrabsInHtml')
            ->withAnyArgs()
            ->andReturnUsing(fn($html, $context) => $html)
            ->byDefault();

        $this->sut = new LetterPreviewService(
            $this->mockRendererManager,
            $this->mockContentStore,
            $this->mockDocTemplateRepo,
            $this->mockVolGrabReplacementService
        );
    }

    public function testRenderPreviewWithoutTemplate(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockSectionRenderer->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('<div class="section">Section content</div>');

        $mockIssueRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('<div class="issue">Issue content</div>');

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockSection = m::mock(LetterInstanceSection::class);
        $mockIssue = $this->createMockIssue('Test Issue Type', 'Issue Type Description', 1);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([$mockIssue]));
        $mockLetterInstance->shouldReceive('getLicence')->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCreatedBy')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')->andReturn(null);

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('<div class="letter-content">', $result);
        $this->assertStringContainsString('<div class="sections">', $result);
        $this->assertStringContainsString('Section content', $result);
        $this->assertStringContainsString('<div class="issues">', $result);
        $this->assertStringContainsString('Issue content', $result);
    }

    public function testRenderPreviewWithTemplate(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockSectionRenderer->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('<p>Section HTML</p>');

        $mockIssueRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('<div class="issue">Issue HTML</div>');

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockSection = m::mock(LetterInstanceSection::class);
        $mockIssue = $this->createMockIssue('Adverts', 'Advert issues', 1);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([$mockIssue]));
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('VOL/LET/12345');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '<html>{{LETTER_REFERENCE}} {{SECTIONS_CONTENT}} {{ISSUES_CONTENT}}</html>';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertStringContainsString('VOL/LET/12345', $result);
        $this->assertStringContainsString('<p>Section HTML</p>', $result);
        $this->assertStringContainsString('Advert issues', $result);
    }

    public function testBuildCaseworkerNameWithUser(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockPerson = m::mock(Person::class);
        $mockPerson->shouldReceive('getForename')->andReturn('John');
        $mockPerson->shouldReceive('getFamilyName')->andReturn('Smith');

        $mockContactDetails = m::mock(ContactDetails::class);
        $mockContactDetails->shouldReceive('getPerson')->andReturn($mockPerson);

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('getContactDetails')->andReturn($mockContactDetails);
        $mockUser->shouldReceive('getId')->andReturn(1);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn($mockUser);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{CASEWORKER_NAME}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertEquals('John Smith', $result);
    }

    public function testBuildCaseworkerNameFallback(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{CASEWORKER_NAME}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertEquals('Caseworker', $result);
    }

    public function testBuildEntityReferenceWithApplication(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockApplication = m::mock(Application::class);
        $mockApplication->shouldReceive('getId')->andReturn(12345);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn($mockApplication);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{ENTITY_REFERENCE}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertEquals('Application: 12345', $result);
    }

    public function testBuildEntityReferenceWithLicence(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->shouldReceive('getName')->andReturn('Test Org');

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB1234567');
        $mockLicence->shouldReceive('getId')->andReturn(999);
        $mockLicence->shouldReceive('getOrganisation')->andReturn($mockOrganisation);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn($mockLicence);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{ENTITY_REFERENCE}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertStringContainsString('Licence: OB1234567', $result);
    }

    public function testBuildSalutationWithOrganisation(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->shouldReceive('getName')->andReturn('ACME Transport Ltd');
        $mockOrganisation->shouldReceive('getId')->andReturn(1);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn($mockOrganisation);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{SALUTATION}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertEquals('<p>Dear ACME Transport Ltd,</p>', $result);
    }

    public function testBuildSalutationFromLicenceOrganisation(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->shouldReceive('getName')->andReturn('Licence Org Ltd');

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB123');
        $mockLicence->shouldReceive('getId')->andReturn(1);
        $mockLicence->shouldReceive('getOrganisation')->andReturn($mockOrganisation);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn($mockLicence);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{SALUTATION}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertEquals('<p>Dear Licence Org Ltd,</p>', $result);
    }

    public function testBuildSalutationFallback(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{SALUTATION}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertEquals('<p>Dear Sir or Madam,</p>', $result);
    }

    public function testBuildDvsaAddressReturnsStaticAddress(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{DVSA_ADDRESS}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertStringContainsString('The Central Licensing Office', $result);
        $this->assertStringContainsString('Hillcrest House', $result);
        $this->assertStringContainsString('Leeds', $result);
        $this->assertStringContainsString('LS9 6NF', $result);
    }

    public function testRenderIssuesGroupedByType(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);

        $mockIssueRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('<div class="issue">Issue content</div>');

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        // Create two issues of same type and one of different type
        $mockIssue1 = $this->createMockIssue('Adverts', 'Advert issues with your application', 1);
        $mockIssue2 = $this->createMockIssue('Adverts', 'Advert issues with your application', 1);
        $mockIssue3 = $this->createMockIssue('Finances', 'Financial issues with your application', 2);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([$mockIssue1, $mockIssue2, $mockIssue3]));
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '{{ISSUES_CONTENT}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        // Should have two issue-type-group divs (Adverts and Finances)
        $this->assertEquals(2, substr_count($result, '<div class="issue-type-group">'));
        // Should have the issue type headings using description
        $this->assertStringContainsString('Advert issues with your application', $result);
        $this->assertStringContainsString('Financial issues with your application', $result);
        // Should have 3 issues rendered
        $this->assertEquals(3, substr_count($result, '<div class="issue">Issue content</div>'));
    }

    public function testRenderSectionsPassesContextToRenderer(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockSection = m::mock(LetterInstanceSection::class);

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getId')->andReturn(123);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB123');
        $mockLicence->shouldReceive('getOrganisation')->andReturn(null);

        $mockApplication = m::mock(Application::class);
        $mockApplication->shouldReceive('getId')->andReturn(456);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn($mockLicence);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn($mockApplication);
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);

        // Verify context is passed with licence and application IDs
        $mockSectionRenderer->shouldReceive('render')
            ->with($mockSection, m::on(function ($context) {
                return isset($context['licence']) && $context['licence'] === 123
                    && isset($context['application']) && $context['application'] === 456;
            }))
            ->once()
            ->andReturn('<div class="section">Content</div>');

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('Content', $result);
    }

    public function testBuildVolGrabContextWithAllEntities(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockSection = m::mock(LetterInstanceSection::class);

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getId')->andReturn(111);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB111');
        $mockLicence->shouldReceive('getOrganisation')->andReturn(null);

        $mockApplication = m::mock(Application::class);
        $mockApplication->shouldReceive('getId')->andReturn(222);

        $mockUser = m::mock(User::class);
        $mockUser->shouldReceive('getId')->andReturn(333);
        $mockUser->shouldReceive('getContactDetails')->andReturn(null);

        $mockCase = m::mock(\Dvsa\Olcs\Api\Entity\Cases\Cases::class);
        $mockCase->shouldReceive('getId')->andReturn(444);

        $mockBusReg = m::mock(\Dvsa\Olcs\Api\Entity\Bus\BusReg::class);
        $mockBusReg->shouldReceive('getId')->andReturn(555);

        $mockOrganisation = m::mock(Organisation::class);
        $mockOrganisation->shouldReceive('getId')->andReturn(666);
        $mockOrganisation->shouldReceive('getName')->andReturn('Test Org');

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn($mockLicence);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn($mockApplication);
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn($mockUser);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn($mockCase);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn($mockBusReg);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn($mockOrganisation);

        // Verify all context IDs are present
        $mockSectionRenderer->shouldReceive('render')
            ->with($mockSection, m::on(function ($context) {
                return $context['licence'] === 111
                    && $context['application'] === 222
                    && $context['user'] === 333
                    && $context['case'] === 444
                    && $context['busRegId'] === 555
                    && $context['organisation'] === 666;
            }))
            ->once()
            ->andReturn('<div class="section">Content</div>');

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('Content', $result);
    }

    public function testBuildVolGrabContextWithNullEntities(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockSection = m::mock(LetterInstanceSection::class);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);

        // Verify context is empty when all entities are null
        $mockSectionRenderer->shouldReceive('render')
            ->with($mockSection, [])
            ->once()
            ->andReturn('<div class="section">Content</div>');

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('Content', $result);
    }

    public function testRenderIssuesPassesContextToRenderer(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockIssue = $this->createMockIssue('Test Type', 'Test Description', 1);

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getId')->andReturn(789);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB789');
        $mockLicence->shouldReceive('getOrganisation')->andReturn(null);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([$mockIssue]));
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn($mockLicence);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);

        // Verify context is passed to issue renderer
        $mockIssueRenderer->shouldReceive('render')
            ->with($mockIssue, m::on(function ($context) {
                return isset($context['licence']) && $context['licence'] === 789;
            }))
            ->once()
            ->andReturn('<div class="issue">Issue content</div>');

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('Issue content', $result);
    }

    public function testRenderPreviewCallsVolGrabReplacementOnFinalHtml(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockSectionRenderer->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('<p>Section content with [[TODAYS_DATE]]</p>');

        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockSection = m::mock(LetterInstanceSection::class);

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('getId')->andReturn(123);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB123');
        $mockLicence->shouldReceive('getOrganisation')->andReturn(null);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')
            ->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')
            ->andReturn($mockLicence);
        $mockLetterInstance->shouldReceive('getOrganisation')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')
            ->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')
            ->andReturn(null);

        $templateContent = '<html>{{SECTIONS_CONTENT}} [[TODAYS_DATE]]</html>';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $this->mockVolGrabReplacementService->shouldReceive('replaceGrabsInHtml')
            ->once()
            ->with(m::on(function ($html) {
                return strpos($html, '[[TODAYS_DATE]]') !== false;
            }), m::on(function ($context) {
                return isset($context['licence']) && $context['licence'] === 123;
            }))
            ->andReturn('<html><p>Section content with 23rd January 2026</p> 23rd January 2026</html>');

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertStringContainsString('23rd January 2026', $result);
        $this->assertStringNotContainsString('[[TODAYS_DATE]]', $result);
    }

    public function testRenderPreviewWithoutTemplateCallsVolGrabReplacement(): void
    {
        $mockSectionRenderer = m::mock(SectionRendererInterface::class);
        $mockSectionRenderer->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('<div class="section">Content</div>');

        $mockIssueRenderer = m::mock(SectionRendererInterface::class);

        $this->mockRendererManager->shouldReceive('get')
            ->with('content-section')
            ->andReturn($mockSectionRenderer);
        $this->mockRendererManager->shouldReceive('get')
            ->with('issue')
            ->andReturn($mockIssueRenderer);

        $mockSection = m::mock(LetterInstanceSection::class);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLicence')->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCreatedBy')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')->andReturn(null);

        // Override default mock to verify vol-grab replacement is called even without template
        $this->mockVolGrabReplacementService->shouldReceive('replaceGrabsInHtml')
            ->once()
            ->with(m::type('string'), [])
            ->andReturnUsing(fn($html, $context) => $html);

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('<div class="letter-content">', $result);
    }

    private function createMockIssue(string $typeName, string $typeDescription, int $typeId): m\MockInterface
    {
        $mockIssueType = m::mock(LetterIssueType::class);
        $mockIssueType->shouldReceive('getId')->andReturn($typeId);
        $mockIssueType->shouldReceive('getName')->andReturn($typeName);
        $mockIssueType->shouldReceive('getDescription')->andReturn($typeDescription);

        $mockIssueVersion = m::mock(LetterIssueVersion::class);
        $mockIssueVersion->shouldReceive('getLetterIssueType')->andReturn($mockIssueType);

        $mockIssue = m::mock(LetterInstanceIssue::class);
        $mockIssue->shouldReceive('getLetterIssueVersion')->andReturn($mockIssueVersion);

        return $mockIssue;
    }
}
