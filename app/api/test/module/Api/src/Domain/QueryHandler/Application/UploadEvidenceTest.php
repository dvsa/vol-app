<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\UploadEvidence;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\ApplicationOperatingCentre as ApplicationOperatingCentreRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Query\Application\UploadEvidence as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * UploadEvidenceTest
 */
class UploadEvidenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UploadEvidence();
        $this->mockRepo('Application', ApplicationRepo::class);
        $this->mockRepo('ApplicationOperatingCentre', ApplicationOperatingCentreRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $documentCollection = new \Doctrine\Common\Collections\ArrayCollection(
            [
                new Document('doc1'),
                new Document('doc2'),
                new Document('doc3'),
            ]
        );

        /** @var ApplicationEntity|m\Mock $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('getPostSubmissionApplicationDocuments')
            ->with('CAT_REF', 'SUB_CAT_REF')->twice()->andReturn($documentCollection);
        $application->shouldReceive('canAddFinancialEvidence')->with()->once()->andReturn('CAN_ADD_FE');

        $this->repoMap['Application']->shouldReceive('fetchById')->with(111)->once()->andReturn($application);
        $this->repoMap['Application']->shouldReceive('getCategoryReference')
            ->with(Category::CATEGORY_APPLICATION)->twice()->andReturn('CAT_REF');
        $this->repoMap['Application']->shouldReceive('getSubCategoryReference')
            ->with(SubCategory::DOC_SUB_CATEGORY_FINANCIAL_EVIDENCE_DIGITAL)->once()->andReturn('SUB_CAT_REF');
        $this->repoMap['Application']->shouldReceive('getSubCategoryReference')
            ->with(SubCategory::DOC_SUB_CATEGORY_SUPPORTING_EVIDENCE)->once()->andReturn('SUB_CAT_REF');

        $aoc1 = m::mock(ApplicationOperatingCentre::class);
        $aoc2 = m::mock(ApplicationOperatingCentre::class);
        $aoc2->shouldReceive('serialize')->with(['operatingCentre' => ['address', 'adDocuments' => ['application']]])
            ->once()->andReturn(['OC1']);
        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchByApplicationOrderByAddress')
            ->with(111)->once()->andReturn([$aoc1, $aoc2]);
        $application->shouldReceive('getApplicationOperatingCentresEvidenceRequired')
            ->with([$aoc1, $aoc2])->once()->andReturn([$aoc2]);

        $result = $this->sut->handleQuery($query);

        foreach ($result['financialEvidence']['documents'] as $index => $document) {
            $this->assertArrayHasKey('identifier', $document);
            $this->assertEquals('doc' . ($index + 1), $document['identifier']);
        }

        foreach ($result['supportingEvidence'] as $index => $document) {
            $this->assertArrayHasKey('identifier', $document);
            $this->assertEquals('doc' . ($index + 1), $document['identifier']);
        }
    }
}
