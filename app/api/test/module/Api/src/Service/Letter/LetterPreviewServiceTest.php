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
use Dvsa\Olcs\Api\Entity\Letter\LetterSection;
use Dvsa\Olcs\Api\Entity\Letter\LetterSectionVersion;
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

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Service\Letter\LetterPreviewService::class)]
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

        $mockAppendixRenderer = m::mock(SectionRendererInterface::class);
        $mockAppendixRenderer->shouldReceive('render')
            ->withAnyArgs()
            ->andReturn('')
            ->byDefault();

        $this->mockRendererManager->shouldReceive('get')
            ->with('appendix')
            ->andReturn($mockAppendixRenderer)
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

        $mockSection = $this->createMockSection();
        $mockIssue = $this->createMockIssue('Test Issue Type', 'Issue Type Description', 1);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([$mockIssue]));
        $mockLetterInstance->shouldReceive('getLicence')->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCreatedBy')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')->andReturn(null);
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('<div class="letter-content">', $result);
        $this->assertStringContainsString('<div class="sections">', $result);
        $this->assertStringContainsString('Section content', $result);
        // Issues are rendered inline via assembly fallback (no __ISSUES__ meta-section)
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

        $mockSection = $this->createMockSection();
        $mockIssue = $this->createMockIssue('Adverts', 'Advert issues', 1);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '<html>{{LETTER_REFERENCE}} {{SECTIONS_CONTENT}} {{ISSUES_CONTENT}}</html>';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
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
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '{{CASEWORKER_NAME}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
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
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '{{CASEWORKER_NAME}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
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
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '{{ENTITY_REFERENCE}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
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
        $mockLicence->shouldReceive('isNi')->andReturn(false)->byDefault();
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB1234567');
        $mockLicence->shouldReceive('getId')->andReturn(999);
        $mockLicence->shouldReceive('getOrganisation')->andReturn($mockOrganisation);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '{{ENTITY_REFERENCE}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
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
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '{{SALUTATION}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
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
        $mockLicence->shouldReceive('isNi')->andReturn(false)->byDefault();
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB123');
        $mockLicence->shouldReceive('getId')->andReturn(1);
        $mockLicence->shouldReceive('getOrganisation')->andReturn($mockOrganisation);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '{{SALUTATION}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
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
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '{{SALUTATION}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertEquals('<p>Dear Sir or Madam,</p>', $result);
    }

    /**
     * VOL-7305: The {{DVSA_ADDRESS}} placeholder used to resolve to a hardcoded
     * Leeds office address. It now resolves to an empty string — the address lives
     * in the headerRightContent slot of the matching MasterTemplate row, picked by
     * MasterTemplateResolver based on the letter's region (GB/NI). This test
     * preserves the backward-compat guarantee that the token is still recognised
     * (no leftover {{DVSA_ADDRESS}} text in the output) but produces nothing.
     */
    public function testDvsaAddressPlaceholderResolvesToEmptyForBackwardCompat(): void
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
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        // Template content is just the legacy placeholder — should resolve to nothing.
        $templateContent = '[start]{{DVSA_ADDRESS}}[end]';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $result = $this->sut->renderPreview($mockLetterInstance, $mockTemplate);

        // Token recognised + replaced with empty
        $this->assertStringNotContainsString('{{DVSA_ADDRESS}}', $result);
        $this->assertStringContainsString('[start][end]', $result);
        // None of the old hardcoded literals leak through
        $this->assertStringNotContainsString('Central Licensing Office', $result);
        $this->assertStringNotContainsString('Hillcrest House', $result);
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
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        // Issues are rendered via assembly fallback into SECTIONS_CONTENT (no __ISSUES__ meta-section)
        $templateContent = '{{SECTIONS_CONTENT}}';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
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

        $mockSection = $this->createMockSection();

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('isNi')->andReturn(false)->byDefault();
        $mockLicence->shouldReceive('getId')->andReturn(123);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB123');
        $mockLicence->shouldReceive('getOrganisation')->andReturn(null);

        $mockApplication = m::mock(Application::class);
        $mockApplication->shouldReceive('getId')->andReturn(456);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        // Verify context is passed with licence and application IDs
        $mockSectionRenderer->shouldReceive('render')
            ->with($mockSection, m::on(fn($context) => isset($context['licence']) && $context['licence'] === 123
                && isset($context['application']) && $context['application'] === 456))
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

        $mockSection = $this->createMockSection();

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('isNi')->andReturn(false)->byDefault();
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
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        // Verify all context IDs are present
        $mockSectionRenderer->shouldReceive('render')
            ->with($mockSection, m::on(fn($context) => $context['licence'] === 111
                && $context['application'] === 222
                && $context['user'] === 333
                && $context['case'] === 444
                && $context['busRegId'] === 555
                && $context['organisation'] === 666))
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

        $mockSection = $this->createMockSection();

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        // Context carries isNi=false (default) even when all linked entities are null
        $mockSectionRenderer->shouldReceive('render')
            ->with($mockSection, ['isNi' => false])
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
        $mockLicence->shouldReceive('isNi')->andReturn(false)->byDefault();
        $mockLicence->shouldReceive('getId')->andReturn(789);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB789');
        $mockLicence->shouldReceive('getOrganisation')->andReturn(null);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        // Verify context is passed to issue renderer
        $mockIssueRenderer->shouldReceive('render')
            ->with($mockIssue, m::on(fn($context) => isset($context['licence']) && $context['licence'] === 789))
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

        $mockSection = $this->createMockSection();

        $mockLicence = m::mock(Licence::class);
        $mockLicence->shouldReceive('isNi')->andReturn(false)->byDefault();
        $mockLicence->shouldReceive('getId')->andReturn(123);
        $mockLicence->shouldReceive('getLicNo')->andReturn('OB123');
        $mockLicence->shouldReceive('getOrganisation')->andReturn(null);

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
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
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        $templateContent = '<html>{{SECTIONS_CONTENT}} [[TODAYS_DATE]]</html>';
        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null)->byDefault();
        $mockTemplate->shouldReceive('getTemplateContent')
            ->andReturn($templateContent);

        $this->mockVolGrabReplacementService->shouldReceive('replaceGrabsInHtml')
            ->once()
            ->with(m::on(fn($html) => str_contains((string) $html, '[[TODAYS_DATE]]')), m::on(fn($context) => isset($context['licence']) && $context['licence'] === 123))
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

        $mockSection = $this->createMockSection();

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')
            ->andReturn(new ArrayCollection([$mockSection]));
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLicence')->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCreatedBy')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')->andReturn(null);
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')
            ->andReturn(new ArrayCollection());

        // Override default mock to verify vol-grab replacement is called even without template.
        // Context now always carries isNi (VOL-7305) even when no licence is attached.
        $this->mockVolGrabReplacementService->shouldReceive('replaceGrabsInHtml')
            ->once()
            ->with(m::type('string'), ['isNi' => false])
            ->andReturnUsing(fn($html, $context) => $html);

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('<div class="letter-content">', $result);
    }

    public function testRenderPreviewToleratesSeedShapedSlotJson(): void
    {
        // The 7.6.0 ETL seed wrote chrome slots without the top-level 'time' and per-block
        // 'id' fields the EditorJS parser mandates. Rendering must normalise rather than 500.
        $sut = new LetterPreviewService(
            $this->mockRendererManager,
            $this->mockContentStore,
            $this->mockDocTemplateRepo,
            $this->mockVolGrabReplacementService,
            new \Dvsa\Olcs\Api\Service\EditorJs\ConverterService()
        );

        $this->mockVolGrabReplacementService->shouldReceive('replaceGrabs')
            ->withAnyArgs()
            ->andReturnUsing(fn($json, $context) => $json);

        $mockRenderer = m::mock(SectionRendererInterface::class);
        $mockRenderer->shouldReceive('render')->withAnyArgs()->andReturn('')->byDefault();
        $this->mockRendererManager->shouldReceive('get')
            ->with(m::type('string'))
            ->andReturn($mockRenderer)
            ->byDefault();

        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')->andReturn(new ArrayCollection());
        $mockLetterInstance->shouldReceive('getReference')->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')->andReturn(null);

        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn(null);
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn([
            // seed shape: no 'time', blocks without 'id'
            'version' => '2.28.2',
            'blocks' => [
                ['type' => 'paragraph', 'data' => ['text' => 'Office of the Traffic Commissioner']],
            ],
        ]);
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null);
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null);
        $mockTemplate->shouldReceive('getTemplateContent')->andReturn('{{HEADER_RIGHT_CONTENT}}');

        $result = $sut->renderPreview($mockLetterInstance, $mockTemplate);

        $this->assertStringContainsString('Office of the Traffic Commissioner', $result);
    }

    public function testOtcLogoFallsBackToLegacySlugWhenRegionSlugMissing(): void
    {
        $sut = $this->createSutWithRealConverter();

        // 1x1 transparent PNG — the purifier verifies data-URI payloads are real images.
        $pngBinary = base64_decode(
            'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
        );

        $mockDocument = m::mock();
        $mockDocument->shouldReceive('getIdentifier')->andReturn('templates/Image/OTClogo.png');

        $mockDocTemplate = m::mock();
        $mockDocTemplate->shouldReceive('getDocument')->andReturn($mockDocument);

        $this->mockDocTemplateRepo->shouldReceive('fetchByTemplateSlug')
            ->with('otclogo-letters-gb')
            ->andReturn(null);
        $this->mockDocTemplateRepo->shouldReceive('fetchByTemplateSlug')
            ->with('otclogo-letters')
            ->andReturn($mockDocTemplate);

        $mockFile = m::mock();
        $mockFile->shouldReceive('getContent')->andReturn($pngBinary);
        $this->mockContentStore->shouldReceive('read')
            ->with('templates/Image/OTClogo.png')
            ->andReturn($mockFile);

        $result = $sut->renderPreview(
            $this->createMinimalLetterInstance(),
            $this->createChromeTemplate(['[[OTC_LOGO]]'], '{{HEADER_LEFT_CONTENT}}')
        );

        $this->assertStringContainsString('data:image/png;base64', $result);
    }

    public function testOtcLogoMissingFileRendersWithoutLogoNotFatal(): void
    {
        // ContentStore::read() returns File|false — a missing file must degrade to a
        // logo-less letter, not fatal with 'getContent() on bool'.
        $spyLogger = m::mock(\Psr\Log\LoggerInterface::class);
        $spyLogger->shouldReceive('warning')->atLeast()->once();
        \Olcs\Logging\Log\Logger::setLogger($spyLogger);

        try {
            $sut = $this->createSutWithRealConverter();

            $mockDocument = m::mock();
            $mockDocument->shouldReceive('getIdentifier')->andReturn('templates/Image/OTClogo.png');
            $mockDocTemplate = m::mock();
            $mockDocTemplate->shouldReceive('getDocument')->andReturn($mockDocument);

            $this->mockDocTemplateRepo->shouldReceive('fetchByTemplateSlug')
                ->andReturn($mockDocTemplate);
            $this->mockContentStore->shouldReceive('read')
                ->with('templates/Image/OTClogo.png')
                ->andReturn(false);

            $result = $sut->renderPreview(
                $this->createMinimalLetterInstance(),
                $this->createChromeTemplate(['[[OTC_LOGO]]'], '{{HEADER_LEFT_CONTENT}}')
            );

            $this->assertStringNotContainsString('[[OTC_LOGO]]', $result);
            $this->assertStringNotContainsString('data:image', $result);
        } finally {
            \Olcs\Logging\Log\Logger::setLogger(new \Psr\Log\NullLogger());
        }
    }

    public function testOtcLogoNonImagePayloadRendersWithoutLogo(): void
    {
        // A stored logo the purifier would reject (SVG/WebP/corrupt) must degrade
        // visibly here, not vanish silently downstream in cleanOutputHtml.
        $spyLogger = m::mock(\Psr\Log\LoggerInterface::class);
        $spyLogger->shouldReceive('warning')->atLeast()->once();
        \Olcs\Logging\Log\Logger::setLogger($spyLogger);

        try {
            $sut = $this->createSutWithRealConverter();

            $mockDocument = m::mock();
            $mockDocument->shouldReceive('getIdentifier')->andReturn('templates/Image/OTClogo.svg');
            $mockDocTemplate = m::mock();
            $mockDocTemplate->shouldReceive('getDocument')->andReturn($mockDocument);

            $this->mockDocTemplateRepo->shouldReceive('fetchByTemplateSlug')
                ->andReturn($mockDocTemplate);

            $mockFile = m::mock();
            $mockFile->shouldReceive('getContent')->andReturn('<svg xmlns="http://www.w3.org/2000/svg"/>');
            $this->mockContentStore->shouldReceive('read')->andReturn($mockFile);

            $result = $sut->renderPreview(
                $this->createMinimalLetterInstance(),
                $this->createChromeTemplate(['[[OTC_LOGO]]'], '{{HEADER_LEFT_CONTENT}}')
            );

            $this->assertStringNotContainsString('data:', $result);
            $this->assertStringNotContainsString('[[OTC_LOGO]]', $result);
        } finally {
            \Olcs\Logging\Log\Logger::setLogger(new \Psr\Log\NullLogger());
        }
    }

    public function testSlotWithUnsupportedBlockTypeRendersEmptyNotFatal(): void
    {
        // The Setono renderer only supports paragraph/header/list; an 'image' or
        // 'table' block in a seeded/imported slot must degrade to an empty slot,
        // not 500 the whole letter.
        $spyLogger = m::mock(\Psr\Log\LoggerInterface::class);
        $spyLogger->shouldReceive('warning')->atLeast()->once();
        \Olcs\Logging\Log\Logger::setLogger($spyLogger);

        try {
            $sut = $this->createSutWithRealConverter();

            $mockTemplate = m::mock(MasterTemplate::class);
            $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn([
                'time' => 1234567890,
                'version' => '2.31.0',
                'blocks' => [
                    ['id' => 'x1', 'type' => 'image', 'data' => ['url' => 'http://example.com/x.png']],
                ],
            ]);
            $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null);
            $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null);
            $mockTemplate->shouldReceive('getFooterContent')->andReturn(null);
            $mockTemplate->shouldReceive('getTemplateContent')
                ->andReturn('BEFORE[{{HEADER_LEFT_CONTENT}}]AFTER');

            $result = $sut->renderPreview($this->createMinimalLetterInstance(), $mockTemplate);

            $this->assertStringContainsString('BEFORE[]AFTER', $result);
        } finally {
            \Olcs\Logging\Log\Logger::setLogger(new \Psr\Log\NullLogger());
        }
    }

    public function testOtcLogoResolutionFailureIsLoggedNotFatal(): void
    {
        $spyLogger = m::mock(\Psr\Log\LoggerInterface::class);
        $spyLogger->shouldReceive('warning')->atLeast()->once();
        \Olcs\Logging\Log\Logger::setLogger($spyLogger);

        try {
            $sut = $this->createSutWithRealConverter();

            $this->mockDocTemplateRepo->shouldReceive('fetchByTemplateSlug')
                ->with('otclogo-letters-gb')
                ->andThrow(new \RuntimeException('doc store down'));
            $this->mockDocTemplateRepo->shouldReceive('fetchByTemplateSlug')
                ->with('otclogo-letters')
                ->andReturn(null);

            $result = $sut->renderPreview(
                $this->createMinimalLetterInstance(),
                $this->createChromeTemplate(['[[OTC_LOGO]]'], '{{HEADER_LEFT_CONTENT}}')
            );

            // Letter still renders, token stripped rather than leaked
            $this->assertStringNotContainsString('[[OTC_LOGO]]', $result);
        } finally {
            \Olcs\Logging\Log\Logger::setLogger(new \Psr\Log\NullLogger());
        }
    }

    private function createSutWithRealConverter(): LetterPreviewService
    {
        $this->mockVolGrabReplacementService->shouldReceive('replaceGrabs')
            ->withAnyArgs()
            ->andReturnUsing(fn($json, $context) => $json)
            ->byDefault();

        $mockRenderer = m::mock(SectionRendererInterface::class);
        $mockRenderer->shouldReceive('render')->withAnyArgs()->andReturn('')->byDefault();
        $this->mockRendererManager->shouldReceive('get')
            ->with(m::type('string'))
            ->andReturn($mockRenderer)
            ->byDefault();

        return new LetterPreviewService(
            $this->mockRendererManager,
            $this->mockContentStore,
            $this->mockDocTemplateRepo,
            $this->mockVolGrabReplacementService,
            new \Dvsa\Olcs\Api\Service\EditorJs\ConverterService()
        );
    }

    private function createMinimalLetterInstance(): m\MockInterface
    {
        $mockLetterInstance = m::mock(LetterInstance::class);
        $mockLetterInstance->shouldReceive('getLetterInstanceSections')->andReturn(new ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')->andReturn(new ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')->andReturn(new ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getLetterInstanceAppendices')->andReturn(new ArrayCollection())->byDefault();
        $mockLetterInstance->shouldReceive('getReference')->andReturn('REF123');
        $mockLetterInstance->shouldReceive('getCreatedBy')->andReturn(null);
        $mockLetterInstance->shouldReceive('getApplication')->andReturn(null);
        $mockLetterInstance->shouldReceive('getLicence')->andReturn(null);
        $mockLetterInstance->shouldReceive('getOrganisation')->andReturn(null);
        $mockLetterInstance->shouldReceive('getCase')->andReturn(null);
        $mockLetterInstance->shouldReceive('getBusReg')->andReturn(null);

        return $mockLetterInstance;
    }

    /**
     * @param string[] $headerLeftParagraphs
     */
    private function createChromeTemplate(array $headerLeftParagraphs, string $templateContent): m\MockInterface
    {
        $blocks = [];
        foreach ($headerLeftParagraphs as $i => $text) {
            $blocks[] = ['id' => 'blk-' . $i, 'type' => 'paragraph', 'data' => ['text' => $text]];
        }

        $mockTemplate = m::mock(MasterTemplate::class);
        $mockTemplate->shouldReceive('getHeaderLeftContent')->andReturn([
            'time' => 1234567890,
            'version' => '2.31.0',
            'blocks' => $blocks,
        ]);
        $mockTemplate->shouldReceive('getHeaderRightContent')->andReturn(null);
        $mockTemplate->shouldReceive('getSignoffContent')->andReturn(null);
        $mockTemplate->shouldReceive('getFooterContent')->andReturn(null);
        $mockTemplate->shouldReceive('getTemplateContent')->andReturn($templateContent);

        return $mockTemplate;
    }

    public function testTodosRenderInBlockContainerNotList(): void
    {
        // VOL-7280: the to-do group must not be a <ul> — each to-do is a block,
        // so bullets inside a to-do's own content stay top-level and solid.
        $mockIssueRenderer = m::mock(SectionRendererInterface::class);
        $mockIssueRenderer->shouldReceive('render')->andReturn('<div class="issue">Issue body</div>');
        $mockTodoRenderer = m::mock(SectionRendererInterface::class);
        $mockTodoRenderer->shouldReceive('render')->andReturn('<div class="todo-item">Upload statements</div>');

        $this->mockRendererManager->shouldReceive('get')->with('issue')->andReturn($mockIssueRenderer);
        $this->mockRendererManager->shouldReceive('get')->with('todo')->andReturn($mockTodoRenderer);
        $this->mockRendererManager->shouldReceive('get')->with('content-section')->andReturn($mockIssueRenderer)->byDefault();

        $mockIssue = $this->createMockIssue('Adverts', 'Advert issues with your application', 1);

        $mockTodo = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterInstanceTodo::class);
        $mockTodo->shouldReceive('getLetterInstanceIssue')->andReturn($mockIssue);

        $mockLetterInstance = $this->createMinimalLetterInstance();
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([$mockIssue]));
        $mockLetterInstance->shouldReceive('getLetterInstanceTodos')
            ->andReturn(new ArrayCollection([$mockTodo]));

        $result = $this->sut->renderPreview($mockLetterInstance, null);

        $this->assertStringContainsString('<div class="todo-list">', $result);
        $this->assertStringNotContainsString('<ul class="todo-list">', $result);
        $this->assertStringContainsString('What you need to do', $result);
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

    private function createMockSection(string $sectionKey = 'test-section'): m\MockInterface
    {
        $mockLetterSection = m::mock(LetterSection::class);
        $mockLetterSection->shouldReceive('getSectionKey')->andReturn($sectionKey);

        $mockSectionVersion = m::mock(LetterSectionVersion::class);
        $mockSectionVersion->shouldReceive('getLetterSection')->andReturn($mockLetterSection);

        $mockSection = m::mock(LetterInstanceSection::class);
        $mockSection->shouldReceive('getLetterSectionVersion')->andReturn($mockSectionVersion);

        return $mockSection;
    }
}
