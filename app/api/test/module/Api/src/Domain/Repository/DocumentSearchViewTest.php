<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\Repository;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Repository\DocumentSearchView as DocumentSearchViewRepo;
use Dvsa\Olcs\Api\Entity\View\DocumentSearchView as Entity;
use Dvsa\Olcs\Transfer\Query\Document\DocumentList;
use Dvsa\Olcs\Utils\Constants\FilterOptions;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\Repository\DocumentSearchView::class)]
final class DocumentSearchViewTest extends RepositoryTestCase
{
    private const string FROM = ' FROM ' . Entity::class . ' m';

    #[\Override]
    public function setUp(): void
    {
        $this->setUpRealSut(DocumentSearchViewRepo::class, true);
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('fetchListProvider')]
    public function testFetchList(array $data, string $expectedWhere): void
    {
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')
            ->with($qb, Query::HYDRATE_ARRAY)
            ->andReturn(['foo' => 'bar']);
        $this->sut->shouldReceive('buildDefaultListQuery');

        $this->assertSame(['foo' => 'bar'], $this->sut->fetchList(DocumentList::create($data)));

        $this->assertSame('SELECT m' . self::FROM . $expectedWhere, $qb->getDQL());
    }

    public static function fetchListProvider(): \Iterator
    {
        yield 'no filters' => [[], ''];

        yield 'every filter, showing related documents too' => [
            [
                'isExternal' => 'Y',
                'category' => 11,
                'documentSubCategory' => 22,
                'licence' => 111,
                'transportManager' => 222,
                'case' => 333,
                'irfoOrganisation' => 444,
                'showDocs' => 'OTHER',
                'format' => 'FOO',
                'onlyUnlinked' => 'Y',
            ],
            // category and documentSubCategory are inlined; the rest bind.
            ' WHERE m.identifier = :identifier AND m.isExternal = :isExternal'
            . ' AND m.category = 11 AND m.documentSubCategory IN(22)'
            . ' AND m.extension = :extension'
            . ' AND (m.licenceId = :licence OR m.tmId = :tm OR m.caseId = :case'
            . ' OR m.irfoOrganisationId = :irfoOrganisation)',
        ];

        // SHOW_SELF_ONLY moves the case filter out of the OR group and ANDs the related ids.
        yield 'self only' => [
            [
                'case' => 'unit_CaseId',
                'irfoOrganisation' => 444,
                'showDocs' => FilterOptions::SHOW_SELF_ONLY,
                'application' => 'unit_AppId',
                'busReg' => 'unit_BusReg',
                'irhpApplication' => 'unit_IrhpApplication',
            ],
            ' WHERE m.applicationId = :APP_ID AND m.caseId = :CASE_ID AND m.busRegId = :BUS_REG_ID'
            . ' AND m.irhpApplicationId = :IRHP_APPLICATION_ID'
            . ' AND m.irfoOrganisationId = :irfoOrganisation',
        ];

        yield 'exclude irhp' => [
            ['licence' => 'unit_LicenceId', 'showDocs' => FilterOptions::EXCLUDE_IRHP],
            ' WHERE m.irhpApplicationId IS NULL AND m.licenceId = :licence',
        ];
    }

    public function testFetchListBindsTheUnlinkedIdentifier(): void
    {
        $qb = $this->createRealQb();

        $this->sut->expects('fetchPaginatedList')->andReturn([]);
        $this->sut->shouldReceive('buildDefaultListQuery');

        $this->sut->fetchList(DocumentList::create(['onlyUnlinked' => 'Y', 'isExternal' => 'Y']));

        $this->assertSame(Entity::IDENTIFIER_UNLINKED, $qb->getParameter('identifier')->getValue());
        $this->assertSame(1, $qb->getParameter('isExternal')->getValue());
    }

    /**
     * Extensions are selected distinctly and then flattened in PHP, dropping blanks.
     */
    public function testFetchDistinctListExtensions(): void
    {
        $qb = $this->createRealQb();
        $qb->stubbedQuery()->expects('getResult')->with(Query::HYDRATE_ARRAY)->andReturn([
            ['extension' => 'pdf'],
            ['extension' => ''],
            ['extension' => 'doc'],
        ]);

        $this->sut->shouldReceive('buildDefaultListQuery');

        $this->assertSame(
            ['pdf', 'doc'],
            $this->sut->fetchDistinctListExtensions(DocumentList::create([])),
        );

        $this->assertSame('SELECT DISTINCT m.extension' . self::FROM, $qb->getDQL());
    }
}
