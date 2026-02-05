<?php

declare(strict_types=1);

/**
 * Financial History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\FinancialHistory;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Query\Application\Application as Qry;
use Mockery as m;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Doctrine\ORM\Query;

/**
 * Financial History Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
class FinancialHistoryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new FinancialHistory();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 111]);

        $mockFinancialDocument = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('serialize')
            ->andReturn(['document' => 'document'])
            ->once()
            ->getMock();

        $mockApplication = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class)
            ->shouldReceive('getApplicationDocuments')
            ->with('category', 'subCategory')
            ->andReturn([$mockFinancialDocument])
            ->once()
            ->shouldReceive('serialize')
            ->andReturn(['application' => 'application'])
            ->once()
            ->getMock();

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query, Query::HYDRATE_OBJECT)
            ->andReturn($mockApplication)
            ->once()
            ->shouldReceive('getCategoryReference')
            ->with(Category::CATEGORY_LICENSING)
            ->andReturn('category')
            ->once()
            ->shouldReceive('getSubCategoryReference')
            ->with(SubCategory::DOC_SUB_CATEGORY_LICENCE_INSOLVENCY_DOCUMENT_DIGITAL)
            ->andReturn('subCategory')
            ->once();

        $expected = [
            'application' => 'application',
            'documents' => [['document' => 'document']]
        ];

        $result = $this->sut->handleQuery($query)->serialize();

        $this->assertEquals($expected, $result);
    }
}
