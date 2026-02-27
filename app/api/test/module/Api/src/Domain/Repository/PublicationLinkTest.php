<?php

declare(strict_types=1);

/**
 * Publication link test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink as PublicationLinkEntity;
use Dvsa\Olcs\Transfer\Query\QueryInterface;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink as PublicationLinkRepo;
use Doctrine\ORM\EntityRepository;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLinkList;
use Dvsa\Olcs\Transfer\Query\Publication\PublicationLinkTmList;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedBusReg;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedApplication;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\UnpublishedPi;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByPi;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByLicence;
use Dvsa\Olcs\Api\Domain\Query\Bookmark\PreviousPublicationByApplication;

/**
 * Publication link test
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class PublicationLinkTest extends RepositoryTestCase
{
    /**
     * @var PublicationLink
     */
    protected $sut;

    public function setUp(): void
    {
        $this->setUpSut(PublicationLinkRepo::class);
    }

    /**
     * @param $qb
     * @return m\MockInterface
     */
    private function getMockRepo(mixed $qb): mixed
    {
        $repo = m::mock(EntityRepository::class);
        $repo->shouldReceive('createQueryBuilder')
            ->with('m')
            ->andReturn($qb);

        return $repo;
    }

    /**
     * @param QueryInterface $query
     * @return m\MockInterface
     */
    private function getPublicationAndSectionQb(mixed $query): m\MockInterface
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('m.publication', ':byPublication')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with(
                'byPublication',
                $query->getPublication()
            )->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')
            ->with('m.publicationSection', ':byPublicationSection')
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('byPublicationSection', $query->getPublicationSection())
            ->once()
            ->andReturnSelf();

        return $mockQb;
    }

    /**
     * @param QueryInterface $query
     * @return m\MockInterface
     */
    private function getPublicationNoPubTypeTaQb(mixed $query): m\MockInterface
    {
        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('p.pubType', ':byPubType')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with(
                'byPubType',
                $query->getPubType()
            )->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('expr->eq')
            ->with('m.trafficArea', ':byTrafficArea')
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('byTrafficArea', $query->getTrafficArea())
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('expr->lt')
            ->with('p.publicationNo', ':byPublicationNo')
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('byPublicationNo', $query->getPublicationNo())
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('orderBy')
            ->with('p.publicationNo', 'DESC')
            ->once()
            ->andReturnSelf();
        $mockQb->shouldReceive('setMaxResults')
            ->with(1)
            ->once()
            ->andReturnSelf();

        return $mockQb;
    }

    /**
     * @param $query
     * @param UnpublishedPi|PreviousPublicationByPi $mockQb
     * @return mixed
     */
    private function addPi(QueryInterface $query, mixed $mockQb): m\MockInterface
    {
        $mockQb->shouldReceive('expr->eq')->with('m.pi', ':byPi')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('byPi', $query->getPi())->once()->andReturnSelf();

        return $mockQb;
    }

    /**
     * @param $mockQb
     * @return mixed
     */
    private function addBus(QueryInterface $query, mixed $mockQb): m\MockInterface
    {
        $mockQb->shouldReceive('expr->eq')->with('m.busReg', ':byBusReg')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('byBusReg', $query->getBusReg())->once()->andReturnSelf();

        return $mockQb;
    }

    /**
     * @param $mockQb
     * @return mixed
     */
    private function addApplication(QueryInterface $query, mixed $mockQb): m\MockInterface
    {
        $mockQb->shouldReceive('expr->eq')->with('m.application', ':byApplication')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('byApplication', $query->getApplication())
            ->once()
            ->andReturnSelf();

        return $mockQb;
    }

    /**
     * @param $mockQb
     * @return mixed
     */
    private function addLicence(QueryInterface $query, mixed $mockQb): m\MockInterface
    {
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':byLicence')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')
            ->with('byLicence', $query->getLicence())
            ->once()
            ->andReturnSelf();

        return $mockQb;
    }

    /**
     * @param $mockQb
     * @param bool|false $results
     * @return mixed
     */
    private function addQueryResult(mixed $mockQb, bool $results = false): m\MockInterface
    {
        if ($results === false) {
            $results = [0 => m::mock(PublicationLinkEntity::class)];
        }

        $mockQb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)->andReturnSelf();

        return $mockQb;
    }

    /**
     * @param $mockQb
     * @param bool|false $results
     * @return mixed
     */
    private function addPreviousPublicationResult(mixed $mockQb, bool $results = false): m\MockInterface
    {
        if ($results === false) {
            $results = [0 => m::mock(PublicationLinkEntity::class)];
        }

        $mockQb->shouldReceive('getQuery->getResult')
            ->with(Query::HYDRATE_OBJECT)
            ->andReturn($results);

        $this->queryBuilder->shouldReceive('modifyQuery')
            ->once()
            ->with($mockQb)->andReturnSelf()
            ->shouldReceive('with')
            ->with('publication', 'p')
            ->once()
            ->andReturnSelf();

        return $mockQb;
    }

    /**
     * Tests fetch unpublished application
     */
    public function testFetchSingleUnpublishedApplication(): void
    {
        $query = UnpublishedApplication::create(
            [
                'publication' => 123,
                'publicationSection' => 456,
                'application' => 789
            ]
        );

        $mockQb = $this->getPublicationAndSectionQb($query);
        $mockQb = $this->addApplication($query, $mockQb);
        $mockQb = $this->addQueryResult($mockQb);

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationLinkEntity::class)
            ->andReturn($repo);

        $this->sut->fetchSingleUnpublished($query);
    }

    /**
     * Tests fetch unpublished pi
     */
    public function testFetchSingleUnpublishedPi(): void
    {
        $query = UnpublishedPi::create(
            [
                'publication' => 123,
                'publicationSection' => 456,
                'pi' => 789
            ]
        );

        $mockQb = $this->getPublicationAndSectionQb($query);
        $mockQb = $this->addPi($query, $mockQb);
        $mockQb = $this->addQueryResult($mockQb);

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationLinkEntity::class)
            ->andReturn($repo);

        $this->sut->fetchSingleUnpublished($query);
    }

    /**
     * Tests fetch unpublished bus
     */
    public function testFetchSingleUnpublishedBus(): void
    {
        $query = UnpublishedBusReg::create(
            [
                'publication' => 123,
                'publicationSection' => 456,
                'busReg' => 789
            ]
        );

        $mockQb = $this->getPublicationAndSectionQb($query);
        $mockQb = $this->addBus($query, $mockQb);
        $mockQb = $this->addQueryResult($mockQb);

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationLinkEntity::class)
            ->andReturn($repo);

        $this->sut->fetchSingleUnpublished($query);
    }

    /**
     * Tests fetch previous publication
     */
    public function testFetchPreviousPublicationPi(): void
    {
        $query = PreviousPublicationByPi::create(
            [
                'publicationNo' => 123,
                'pubType' => 'N&P',
                'trafficArea' => 'M',
                'pi' => 789
            ]
        );

        $mockQb = $this->getPublicationNoPubTypeTaQb($query);
        $mockQb = $this->addPi($query, $mockQb);
        $mockQb = $this->addPreviousPublicationResult($mockQb);

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationLinkEntity::class)
            ->andReturn($repo);

        $this->sut->fetchPreviousPublicationNo($query);
    }

    /**
     * Tests fetch previous publication
     */
    public function testFetchPreviousPublicationApp(): void
    {
        $query = PreviousPublicationByApplication::create(
            [
                'publicationNo' => 123,
                'pubType' => 'N&P',
                'trafficArea' => 'M',
                'application' => 789
            ]
        );

        $mockQb = $this->getPublicationNoPubTypeTaQb($query);
        $mockQb = $this->addApplication($query, $mockQb);
        $mockQb = $this->addPreviousPublicationResult($mockQb);

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationLinkEntity::class)
            ->andReturn($repo);

        $this->sut->fetchPreviousPublicationNo($query);
    }

    /**
     * Tests fetch previous publication
     */
    public function testFetchPreviousPublicationLic(): void
    {
        $query = PreviousPublicationByLicence::create(
            [
                'publicationNo' => 123,
                'pubType' => 'N&P',
                'trafficArea' => 'M',
                'licence' => 789
            ]
        );

        $mockQb = $this->getPublicationNoPubTypeTaQb($query);
        $mockQb = $this->addLicence($query, $mockQb);
        $mockQb = $this->addPreviousPublicationResult($mockQb);

        /** @var EntityRepository $repo */
        $repo = $this->getMockRepo($mockQb);

        $this->em->shouldReceive('getRepository')
            ->with(PublicationLinkEntity::class)
            ->andReturn($repo);

        $this->sut->fetchPreviousPublicationNo($query);
    }

    /**
     * Tests the tm list filter is applied correctly
     */
    public function testApplyListFiltersTm(): void
    {
        $sut = m::mock(PublicationLinkRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $transportManager = 11;

        $query = PublicationLinkTmList::create(['transportManager' => $transportManager]);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('m.transportManager', ':transportManager')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('transportManager', $transportManager)->once()->andReturnSelf();

        $sut->applyListFilters($mockQb, $query);
    }

    /**
     * Tests the licence list filter is applied correctly
     */
    public function testApplyListFiltersLicence(): void
    {
        $sut = m::mock(PublicationLinkRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $licence = 22;

        $query = PublicationLinkList::create(['licence' => $licence]);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('m.licence', ':licence')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('licence', $licence)->once()->andReturnSelf();

        $sut->applyListFilters($mockQb, $query);
    }

    /**
     * Tests the applciation list filter is applied correctly
     */
    public function testApplyListFiltersApplication(): void
    {
        $sut = m::mock(PublicationLinkRepo::class)->makePartial()->shouldAllowMockingProtectedMethods();

        $application = 610;

        $query = PublicationLinkList::create(['application' => $application]);

        $mockQb = m::mock(QueryBuilder::class);
        $mockQb->shouldReceive('expr->eq')->with('m.application', ':application')->once()->andReturnSelf();
        $mockQb->shouldReceive('andWhere')->once()->andReturnSelf();
        $mockQb->shouldReceive('setParameter')->with('application', $application)->once()->andReturnSelf();

        $sut->applyListFilters($mockQb, $query);
    }

    public function testFetchIneligiblePiPublicationLinks(): void
    {
        $publicationEntityId = 1;
        $today = (new Datetime())->format('Y-m-d');
        $mockQb = $this->createMockQb('[QUERY]');
        $this->mockCreateQueryBuilder($mockQb);

        $mockQb->shouldReceive('getQuery->getResult');

        $expectedQuery = '[QUERY]' .
            ' AND m.publication = [[' . $publicationEntityId . ']]' .
            ' AND m.publishAfterDate IS NOT NULL' .
            ' AND m.publishAfterDate > [[' . $today . ']]';

        /** @var PublicationEntity $publicationEntity */
        $publicationEntity = m::mock(PublicationEntity::class)->makePartial();
        $publicationEntity->setId(1);
        $this->sut->fetchIneligiblePublicationLinks($publicationEntity);
        $this->assertEquals($expectedQuery, $this->query);
    }
}
