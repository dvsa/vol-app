<?php

declare(strict_types=1);

/**
 * Document test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\Document as DocumentRepo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Mockery as m;

/**
 * Document Test
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class DocumentTest extends RepositoryTestCase
{
    public function setUp(): void
    {
        $this->setUpSut(DocumentRepo::class);
    }

    public function testFetchListForTm(): void
    {
        $sut = m::mock(DocumentRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $mockQb->shouldReceive('expr->eq')->with('m.category', ':category')->once()->andReturn('category');
        $mockQb->shouldReceive('andWhere')->with('category')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('category', CategoryEntity::CATEGORY_TRANSPORT_MANAGER)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.subCategory', ':subCategory')->once()->andReturn('subCategory');
        $mockQb->shouldReceive('andWhere')->with('subCategory')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('subCategory', CategoryEntity::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.transportManager', ':transportManager')->once()->andReturn('tm');
        $mockQb->shouldReceive('andWhere')->with('tm')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('transportManager', 1)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('orderBy')->with('m.id', 'DESC')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                    ->shouldReceive('execute')
                    ->andReturn('result')
                    ->once()
                    ->getMock()
            )
            ->once()
            ->getMock();

        $sut->shouldReceive('createQueryBuilder')
            ->andReturn($mockQb)
            ->once()
            ->getMock();

        $this->assertEquals('result', $sut->fetchListForTm(1));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('tmProvider')]
    public function testFetchListForTmApplication(mixed $type): void
    {
        $sut = m::mock(DocumentRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        /** @var QueryBuilder $qb */
        $mockQb = m::mock(QueryBuilder::class);

        $mockQb->shouldReceive('expr->eq')->with('m.category', ':category')->once()->andReturn('category');
        $mockQb->shouldReceive('andWhere')->with('category')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('category', CategoryEntity::CATEGORY_TRANSPORT_MANAGER)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.subCategory', ':subCategory')->once()->andReturn('subCategory');
        $mockQb->shouldReceive('andWhere')->with('subCategory')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('subCategory', CategoryEntity::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.transportManager', ':transportManager')->once()->andReturn('tm');
        $mockQb->shouldReceive('andWhere')->with('tm')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('transportManager', 1)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('expr->eq')->with('m.' . $type, ':' . $type)->once()->andReturn('tm');
        $mockQb->shouldReceive('andWhere')->with('tm')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with($type, 2)
            ->once()
            ->andReturnSelf();

        $mockQb->shouldReceive('orderBy')->with('m.id', 'DESC')->once()->andReturnSelf();

        $mockQb->shouldReceive('getQuery')
            ->andReturn(
                m::mock()
                ->shouldReceive('execute')
                ->andReturn('result')
                ->once()
                ->getMock()
            )
            ->once()
            ->getMock();

        $sut->shouldReceive('createQueryBuilder')
            ->andReturn($mockQb)
            ->once()
            ->getMock();

        if ($type == 'licence') {
            $this->assertEquals('result', $sut->fetchListForTmLicence(1, 2));
        } else {
            $this->assertEquals('result', $sut->fetchListForTmApplication(1, 2));
        }
    }

    public static function tmProvider(): array
    {
        return [
            ['licence'],
            ['application'],
        ];
    }

    public function testFetchUnlinkedOcDocumentsForEntity(): void
    {
        $category = m::mock(Category::class)->makePartial();
        $subCategory = m::mock(SubCategory::class)->makePartial();

        $this->em->shouldReceive('getReference')
            ->with(Category::class, Category::CATEGORY_APPLICATION)
            ->andReturn($category);

        $this->em->shouldReceive('getReference')
            ->with(SubCategory::class, Category::DOC_SUB_CATEGORY_APPLICATION_ADVERT_DIGITAL)
            ->andReturn($subCategory);

        /** @var Document $doc1 */
        $doc1 = m::mock(Document::class)->makePartial();

        /** @var Document $doc2 */
        $doc2 = m::mock(Document::class)->makePartial();
        $doc2->setCategory($category);
        $doc2->setSubCategory($subCategory);

        $docs = new ArrayCollection();
        $docs->add($doc1);
        $docs->add($doc2);

        /** @var Application $entity */
        $entity = m::mock(Application::class)->makePartial();
        $entity->setDocuments($docs);

        $collection = $this->sut->fetchUnlinkedOcDocumentsForEntity($entity);

        $this->assertEquals(1, $collection->count());
        $this->assertEquals($doc2, $collection->first());
    }

    public function testFetchListForContinuationDetail(): void
    {
        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('getResult')->with(Query::HYDRATE_OBJECT)->once()->andReturn('RESULT')
                ->getMock()
        );
        static::assertEquals('RESULT', $this->sut->fetchListForContinuationDetail(95));

        $expectedQuery = 'BLAH AND m.continuationDetail = [[95]] ORDER BY m.id DESC';

        static::assertEquals($expectedQuery, $this->query);
    }

    public function testFetchListForStatement(): void
    {
        $qb = $this->createMockQb('BLAH');
        $this->mockCreateQueryBuilder($qb);

        $qb->shouldReceive('getQuery')->andReturn(
            m::mock()->shouldReceive('getResult')->with(Query::HYDRATE_OBJECT)->once()->andReturn('RESULT')
                ->getMock()
        );
        static::assertEquals('RESULT', $this->sut->fetchListForStatement(123));

        $expectedQuery = 'BLAH AND m.statement = [[123]]';

        static::assertEquals($expectedQuery, $this->query);
    }
}
