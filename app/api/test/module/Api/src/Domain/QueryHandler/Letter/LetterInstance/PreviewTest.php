<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Letter\LetterInstance;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Letter\LetterInstance\Preview as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\LetterInstance as LetterInstanceRepo;
use Dvsa\Olcs\Api\Domain\Repository\MasterTemplate as MasterTemplateRepo;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstance as LetterInstanceEntity;
use Dvsa\Olcs\Api\Entity\Letter\LetterInstanceIssue;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueType;
use Dvsa\Olcs\Api\Entity\Letter\LetterIssueVersion;
use Dvsa\Olcs\Api\Entity\Letter\LetterType;
use Dvsa\Olcs\Api\Entity\Letter\MasterTemplate;
use Dvsa\Olcs\Api\Service\Letter\LetterPreviewService;
use Dvsa\Olcs\Api\Service\Letter\MasterTemplateResolver;
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\Preview as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Preview LetterInstance QueryHandler Test
 */
class PreviewTest extends QueryHandlerTestCase
{
    private m\MockInterface|LetterPreviewService $mockPreviewService;
    private m\MockInterface|MasterTemplateResolver $mockMasterTemplateResolver;

    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('LetterInstance', LetterInstanceRepo::class);
        $this->mockRepo('MasterTemplate', MasterTemplateRepo::class);

        $this->mockPreviewService = m::mock(LetterPreviewService::class);
        $this->mockMasterTemplateResolver = m::mock(MasterTemplateResolver::class);

        $this->mockedSmServices = [
            LetterPreviewService::class => $this->mockPreviewService,
            MasterTemplateResolver::class => $this->mockMasterTemplateResolver,
        ];

        parent::setUp();
    }

    public function testHandleQueryUsesResolvedMasterTemplate(): void
    {
        // VOL-7305: handler delegates master-template selection to MasterTemplateResolver.
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockMasterTemplate = m::mock(MasterTemplate::class);

        $mockLetterType = m::mock(LetterType::class);
        $mockLetterInstance = $this->createMockLetterInstance($mockLetterType);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterInstance);

        $this->mockMasterTemplateResolver->shouldReceive('resolve')
            ->with($mockLetterInstance)
            ->once()
            ->andReturn($mockMasterTemplate);

        $this->mockPreviewService->shouldReceive('renderPreview')
            ->with($mockLetterInstance, $mockMasterTemplate)
            ->once()
            ->andReturn('<html>VOL/LET/123</html>');

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('letterInstance', $result);
        $this->assertArrayHasKey('previewHtml', $result);
        $this->assertArrayHasKey('sectionsList', $result);
        $this->assertEquals('<html>VOL/LET/123</html>', $result['previewHtml']);
    }

    public function testHandleQueryWhenResolverReturnsNull(): void
    {
        // VOL-7305: if the resolver finds nothing, renderPreview gets null and falls
        // back to its templateless rendering path.
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockLetterType = m::mock(LetterType::class);
        $mockLetterInstance = $this->createMockLetterInstance($mockLetterType);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterInstance);

        $this->mockMasterTemplateResolver->shouldReceive('resolve')
            ->with($mockLetterInstance)
            ->once()
            ->andReturn(null);

        $this->mockPreviewService->shouldReceive('renderPreview')
            ->with($mockLetterInstance, null)
            ->once()
            ->andReturn('<div class="letter-content">Preview without template</div>');

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result);
        $this->assertStringContainsString('Preview without template', $result['previewHtml']);
    }

    public function testBuildSectionsListGroupsByIssueType(): void
    {
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockMasterTemplate = m::mock(MasterTemplate::class);

        $mockLetterType = m::mock(LetterType::class);

        $this->mockMasterTemplateResolver->shouldReceive('resolve')
            ->andReturn($mockMasterTemplate);

        // Create issues with different types
        $mockIssueType1 = m::mock(LetterIssueType::class);
        $mockIssueType1->shouldReceive('getId')->andReturn(1);
        $mockIssueType1->shouldReceive('getName')->andReturn('Adverts');

        $mockIssueType2 = m::mock(LetterIssueType::class);
        $mockIssueType2->shouldReceive('getId')->andReturn(2);
        $mockIssueType2->shouldReceive('getName')->andReturn('Finances');

        $mockIssueVersion1 = m::mock(LetterIssueVersion::class);
        $mockIssueVersion1->shouldReceive('getLetterIssueType')->andReturn($mockIssueType1);

        $mockIssueVersion2 = m::mock(LetterIssueVersion::class);
        $mockIssueVersion2->shouldReceive('getLetterIssueType')->andReturn($mockIssueType1);

        $mockIssueVersion3 = m::mock(LetterIssueVersion::class);
        $mockIssueVersion3->shouldReceive('getLetterIssueType')->andReturn($mockIssueType2);

        $mockIssue1 = m::mock(LetterInstanceIssue::class);
        $mockIssue1->shouldReceive('getLetterIssueVersion')->andReturn($mockIssueVersion1);
        $mockIssue1->shouldReceive('requiresInput')->andReturn(false);
        $mockIssue1->shouldReceive('hasBeenEdited')->andReturn(false);

        $mockIssue2 = m::mock(LetterInstanceIssue::class);
        $mockIssue2->shouldReceive('getLetterIssueVersion')->andReturn($mockIssueVersion2);
        $mockIssue2->shouldReceive('requiresInput')->andReturn(false);
        $mockIssue2->shouldReceive('hasBeenEdited')->andReturn(false);

        $mockIssue3 = m::mock(LetterInstanceIssue::class);
        $mockIssue3->shouldReceive('getLetterIssueVersion')->andReturn($mockIssueVersion3);
        $mockIssue3->shouldReceive('requiresInput')->andReturn(false);
        $mockIssue3->shouldReceive('hasBeenEdited')->andReturn(false);

        $mockLetterInstance = m::mock(LetterInstanceEntity::class);
        $mockLetterInstance->shouldReceive('getLetterType')
            ->andReturn($mockLetterType);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([$mockIssue1, $mockIssue2, $mockIssue3]));
        $mockLetterInstance->shouldReceive('serialize')
            ->andReturn(['id' => 123]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterInstance);

        $this->mockPreviewService->shouldReceive('renderPreview')
            ->once()
            ->andReturn('<html>Preview</html>');

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result);
        $this->assertCount(2, $result['sectionsList']);

        // Check that we have both unique issue types
        $typeNames = array_column($result['sectionsList'], 'name');
        $this->assertContains('Adverts', $typeNames);
        $this->assertContains('Finances', $typeNames);

        // Verify all items have the expected structure
        foreach ($result['sectionsList'] as $section) {
            $this->assertArrayHasKey('id', $section);
            $this->assertArrayHasKey('name', $section);
            $this->assertArrayHasKey('type', $section);
            $this->assertEquals('issueType', $section['type']);
        }
    }

    public function testBuildSectionsListReturnsEmptyArrayWhenNoIssues(): void
    {
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockMasterTemplate = m::mock(MasterTemplate::class);

        $mockLetterType = m::mock(LetterType::class);

        $this->mockMasterTemplateResolver->shouldReceive('resolve')
            ->andReturn($mockMasterTemplate);

        $mockLetterInstance = m::mock(LetterInstanceEntity::class);
        $mockLetterInstance->shouldReceive('getLetterType')
            ->andReturn($mockLetterType);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([]));
        $mockLetterInstance->shouldReceive('serialize')
            ->andReturn(['id' => 123]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterInstance);

        $this->mockPreviewService->shouldReceive('renderPreview')
            ->once()
            ->andReturn('<html>Preview</html>');

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result['sectionsList']);
        $this->assertEmpty($result['sectionsList']);
    }

    private function createMockLetterInstance(m\MockInterface $letterType): m\MockInterface
    {
        $mockLetterInstance = m::mock(LetterInstanceEntity::class);
        $mockLetterInstance->shouldReceive('getLetterType')
            ->andReturn($letterType);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([]));
        $mockLetterInstance->shouldReceive('serialize')
            ->andReturn(['id' => 123, 'reference' => 'VOL/LET/123']);

        return $mockLetterInstance;
    }

    public function testSectionsListFlagsPendingRequiredInput(): void
    {
        // VOL-7402: the sidebar needs to know which issue types still contain
        // unedited "requires input" content so it can highlight them.
        $query = Qry::create(['id' => 123]);

        $mockIssueType = m::mock(LetterIssueType::class);
        $mockIssueType->shouldReceive('getId')->andReturn(1);
        $mockIssueType->shouldReceive('getName')->andReturn('Finances');

        $mockIssueVersion = m::mock(LetterIssueVersion::class);
        $mockIssueVersion->shouldReceive('getLetterIssueType')->andReturn($mockIssueType);

        $pendingIssue = m::mock(LetterInstanceIssue::class);
        $pendingIssue->shouldReceive('getLetterIssueVersion')->andReturn($mockIssueVersion);
        $pendingIssue->shouldReceive('requiresInput')->andReturn(true);
        $pendingIssue->shouldReceive('hasBeenEdited')->andReturn(false);

        $mockLetterType = m::mock(\Dvsa\Olcs\Api\Entity\Letter\LetterType::class);
        $mockLetterType->shouldReceive('getMasterTemplate')->andReturn(null);

        $mockLetterInstance = m::mock(LetterInstanceEntity::class);
        $mockLetterInstance->shouldReceive('getLetterType')->andReturn($mockLetterType);
        $mockLetterInstance->shouldReceive('getLetterInstanceIssues')
            ->andReturn(new ArrayCollection([$pendingIssue]));
        $mockLetterInstance->shouldReceive('serialize')->andReturn(['id' => 123]);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterInstance);

        $this->mockMasterTemplateResolver->shouldReceive('resolve')
            ->andReturn(null);

        $this->mockPreviewService->shouldReceive('renderPreview')
            ->once()
            ->andReturn('<html>Preview</html>');

        $result = $this->sut->handleQuery($query);

        $this->assertTrue($result['sectionsList'][0]['inputPending']);
    }
}
