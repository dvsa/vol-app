<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpApplication;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpApplication\Documents;
use Dvsa\Olcs\Api\Domain\Repository\IrhpApplication as IrhpApplicationRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpApplication as IrhpApplicationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Transfer\Query\IrhpApplication\Documents as DocumentsQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class DocumentsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = m::mock(Documents::class)
             ->makePartial()
             ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('IrhpApplication', IrhpApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $irhpApplicationId = 20;
        $categoryId = 4;
        $subCategoryId = 65;

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

        $irhpApplication = m::mock(IrhpApplicationEntity::class);
        $irhpApplication->shouldReceive('getDocumentsByCategoryAndSubCategory')
            ->with($categoryId, $subCategoryId)
            ->andReturn($documents);

        $query = DocumentsQry::create(
            [
                'id' => $irhpApplicationId,
                'category' => $categoryId,
                'subCategory' => $subCategoryId,
            ]
        );

        $this->sut->shouldReceive('resultList')
            ->with($documents)
            ->andReturn($bundledDocuments);

        $this->repoMap['IrhpApplication']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($irhpApplication);

        $this->assertEquals(
            $bundledDocuments,
            $this->sut->handleQuery($query)
        );
    }
}
