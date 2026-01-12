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
use Dvsa\Olcs\Transfer\Query\Letter\LetterInstance\Preview as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Preview LetterInstance QueryHandler Test
 */
class PreviewTest extends QueryHandlerTestCase
{
    private m\MockInterface|LetterPreviewService $mockPreviewService;

    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('LetterInstance', LetterInstanceRepo::class);
        $this->mockRepo('MasterTemplate', MasterTemplateRepo::class);

        $this->mockPreviewService = m::mock(LetterPreviewService::class);

        $this->mockedSmServices = [
            LetterPreviewService::class => $this->mockPreviewService,
        ];

        parent::setUp();
    }

    public function testHandleQueryWithMasterTemplateFromLetterType(): void
    {
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockMasterTemplate = m::mock(MasterTemplate::class);
        $mockMasterTemplate->shouldReceive('getTemplateContent')
            ->andReturn('<html>{{LETTER_REFERENCE}}</html>');

        $mockLetterType = m::mock(LetterType::class);
        $mockLetterType->shouldReceive('getMasterTemplate')
            ->andReturn($mockMasterTemplate);

        $mockLetterInstance = $this->createMockLetterInstance($mockLetterType);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterInstance);

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

    public function testHandleQueryFallsBackToDefaultTemplate(): void
    {
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockLetterType = m::mock(LetterType::class);
        $mockLetterType->shouldReceive('getMasterTemplate')
            ->andReturn(null);

        $mockDefaultTemplate = m::mock(MasterTemplate::class);

        $mockLetterInstance = $this->createMockLetterInstance($mockLetterType);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterInstance);

        $this->repoMap['MasterTemplate']->shouldReceive('fetchList')
            ->once()
            ->andReturn([$mockDefaultTemplate]);

        $this->mockPreviewService->shouldReceive('renderPreview')
            ->with($mockLetterInstance, $mockDefaultTemplate)
            ->once()
            ->andReturn('<html>Preview HTML</html>');

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result);
        $this->assertEquals('<html>Preview HTML</html>', $result['previewHtml']);
    }

    public function testHandleQueryWithNoTemplateAvailable(): void
    {
        $data = ['id' => 123];
        $query = Qry::create($data);

        $mockLetterType = m::mock(LetterType::class);
        $mockLetterType->shouldReceive('getMasterTemplate')
            ->andReturn(null);

        $mockLetterInstance = $this->createMockLetterInstance($mockLetterType);

        $this->repoMap['LetterInstance']->shouldReceive('fetchUsingId')
            ->with($query)
            ->once()
            ->andReturn($mockLetterInstance);

        $this->repoMap['MasterTemplate']->shouldReceive('fetchList')
            ->once()
            ->andReturn([]);

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
        $mockLetterType->shouldReceive('getMasterTemplate')
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

        $mockIssue2 = m::mock(LetterInstanceIssue::class);
        $mockIssue2->shouldReceive('getLetterIssueVersion')->andReturn($mockIssueVersion2);

        $mockIssue3 = m::mock(LetterInstanceIssue::class);
        $mockIssue3->shouldReceive('getLetterIssueVersion')->andReturn($mockIssueVersion3);

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
        $mockLetterType->shouldReceive('getMasterTemplate')
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
}
