<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Documents;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\Documents as DocumentsQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class DocumentsTest extends QueryHandlerTestCase
{
    #[\Override]
    protected function initReferences(): void
    {
        $this->categoryReferences = [
            Category::CATEGORY_APPLICATION => m::mock(Category::class),
        ];

        $this->subCategoryReferences = [
            SubCategory::DOC_SUB_CATEGORY_LARGE_PSV_EVIDENCE_DIGITAL => m::mock(SubCategory::class),
        ];

        parent::initReferences();
    }

    public function setUp(): void
    {
        $this->sut = m::mock(Documents::class)->makePartial()->shouldAllowMockingProtectedMethods();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $documents = new ArrayCollection(
            [
                m::mock(DocumentEntity::class),
                m::mock(DocumentEntity::class)
            ]
        );

        $bundledDocuments = [
            [
                'id' => 123,
                'prop1' => 'value1',
                'prop2' => 'value2',
            ],
            [
                'id' => 456,
                'prop1' => 'value3',
                'prop2' => 'value4',
            ],
        ];

        $application = m::mock(ApplicationEntity::class);
        $application->expects('getApplicationDocuments')
            ->with(
                $this->categoryReferences[Category::CATEGORY_APPLICATION],
                $this->subCategoryReferences[SubCategory::DOC_SUB_CATEGORY_LARGE_PSV_EVIDENCE_DIGITAL]
            )->andReturn($documents);

        $query = DocumentsQry::create(
            [
                'id' => 999,
                'category' => Category::CATEGORY_APPLICATION,
                'subCategory' => SubCategory::DOC_SUB_CATEGORY_LARGE_PSV_EVIDENCE_DIGITAL,
            ]
        );

        $this->sut->expects('resultList')->with($documents)->andReturn($bundledDocuments);

        $this->repoMap['Application']->expects('fetchUsingId')->with($query)->andReturn($application);

        $this->repoMap['Application']
            ->expects('getCategoryReference')
            ->with(Category::CATEGORY_APPLICATION)
            ->andReturn($this->categoryReferences[Category::CATEGORY_APPLICATION]);

        $this->repoMap['Application']
            ->expects('getSubCategoryReference')
            ->with(SubCategory::DOC_SUB_CATEGORY_LARGE_PSV_EVIDENCE_DIGITAL)
            ->andReturn($this->subCategoryReferences[SubCategory::DOC_SUB_CATEGORY_LARGE_PSV_EVIDENCE_DIGITAL]);

        $this->assertEquals(
            $bundledDocuments,
            $this->sut->handleQuery($query)
        );
    }
}
