<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\Document as Repo;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Doc\Document as Entity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Mockery as m;

final class DocumentTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    private const string TM_WHERE = ' WHERE m.category = :category'
        . ' AND m.subCategory = :subCategory AND m.transportManager = :transportManager';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(Repo::class, true);
    }

    public function testFetchListForTm(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForTm(1));

        $this->assertSame(
            'SELECT m' . self::FROM . self::TM_WHERE . ' ORDER BY m.id DESC',
            $qb->getDQL(),
        );
        $this->assertSame(Category::CATEGORY_TRANSPORT_MANAGER, $qb->getParameter('category')->getValue());
        $this->assertSame(
            Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_CPC_OR_EXEMPTION,
            $qb->getParameter('subCategory')->getValue(),
        );
        $this->assertSame(1, $qb->getParameter('transportManager')->getValue());
    }

    /**
     * The application and licence variants share one implementation, differing only in which
     * column the second id is matched against.
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('tmParentProvider')]
    public function testFetchListForTmApplicationOrLicence(string $method, string $type): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}(1, 2));

        $this->assertSame(
            'SELECT m' . self::FROM . self::TM_WHERE . ' AND m.' . $type . ' = :' . $type
            . ' ORDER BY m.id DESC',
            $qb->getDQL(),
        );
        $this->assertSame(
            Category::DOC_SUB_CATEGORY_TRANSPORT_MANAGER_TM1_ASSISTED_DIGITAL,
            $qb->getParameter('subCategory')->getValue(),
        );
        $this->assertSame(2, $qb->getParameter($type)->getValue());
    }

    public static function tmParentProvider(): \Iterator
    {
        yield 'application' => ['fetchListForTmApplication', 'application'];
        yield 'licence' => ['fetchListForTmLicence', 'licence'];
    }

    public function testFetchUnassignedListForUser(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('execute')->withNoArgs()->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchUnassignedListForUser(7));

        // The created-by id is inlined rather than bound.
        $this->assertSame(
            'SELECT m' . self::FROM
            . ' WHERE m.messagingMessage IS NULL AND m.createdBy = 7'
            . ' ORDER BY m.id DESC',
            $qb->getDQL(),
        );
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('singleFilterProvider')]
    public function testFetchByColumn(string $method, mixed $value, string $expectedWhere, string $parameter): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->{$method}($value));

        $this->assertSame('SELECT m' . self::FROM . ' WHERE ' . $expectedWhere, $qb->getDQL());
        $this->assertSame($value, $qb->getParameter($parameter)->getValue());
    }

    public static function singleFilterProvider(): \Iterator
    {
        yield 'by statement' => [
            'fetchListForStatement',
            5,
            'm.statement = :statementId',
            'statementId',
        ];
        yield 'by document store id' => [
            'fetchByDocumentStoreId',
            'abc',
            'm.identifier = :documentStoreId',
            'documentStoreId',
        ];
    }

    public function testFetchListForContinuationDetail(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_OBJECT)->andReturn(['RESULTS']);

        $this->assertSame(['RESULTS'], $this->sut->fetchListForContinuationDetail(5));

        $this->assertSame(
            'SELECT m' . self::FROM . ' WHERE m.continuationDetail = :continuationDetail'
            . ' ORDER BY m.id DESC',
            $qb->getDQL(),
        );
    }

    /**
     * This one filters an already-loaded collection with a Criteria rather than building DQL, so
     * there is no query to assert — only the matching.
     */
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

        $unmatched = m::mock(Entity::class)->makePartial();

        $matched = m::mock(Entity::class)->makePartial();
        $matched->setCategory($category);
        $matched->setSubCategory($subCategory);

        $entity = m::mock(Application::class)->makePartial();
        $entity->setDocuments(new ArrayCollection([$unmatched, $matched]));

        $collection = $this->sut->fetchUnlinkedOcDocumentsForEntity($entity);

        $this->assertSame(1, $collection->count());
        $this->assertSame($matched, $collection->first());
    }
}
