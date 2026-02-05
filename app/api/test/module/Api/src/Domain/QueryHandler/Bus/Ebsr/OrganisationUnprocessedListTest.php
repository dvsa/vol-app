<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus\Ebsr;

use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr\OrganisationUnprocessedList;
use Dvsa\Olcs\Api\Domain\QueryHandler\ResultList;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\OrganisationUnprocessedList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Identity\IdentityInterface;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * OrganisationUnprocessedList Test
 */
class OrganisationUnprocessedListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new OrganisationUnprocessedList();
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    /**
     * Tests a query when organisation exists
     */
    public function testHandleQuery(): void
    {
        $query = Qry::create([]);

        $organisationId = 1245;
        $organisation = m::mock(OrganisationEntity::class);
        $organisation->shouldReceive('getId')->andReturn($organisationId);

        $identity = m::mock(IdentityInterface::class);
        $identity->shouldReceive('getUser->getOrganisationUsers->isEmpty')->once()->andReturn(false);
        $identity->shouldReceive('getUser->getRelatedOrganisation')->once()->andReturn($organisation);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $document1 = m::mock(DocumentEntity::class)->makePartial();
        $document1->setId(1);

        $document2 = m::mock(DocumentEntity::class)->makePartial();
        $document2->setId(2);

        $ebsrSub1 = m::mock(EbsrSubmissionEntity::class);
        $ebsrSub1->shouldReceive('getDocument')->once()->andReturn($document1);

        $ebsrSub2 = m::mock(EbsrSubmissionEntity::class);
        $ebsrSub2->shouldReceive('getDocument')->once()->andReturn($document2);

        $searchResults = [
            0 => $ebsrSub1,
            1 => $ebsrSub2,
        ];

        $expectedDocuments = [
            0 => $document1,
            1 => $document2
        ];

        $expectedQueryResult = (new ResultList($expectedDocuments, []))->serialize();

        $this->repoMap['EbsrSubmission']->shouldReceive('fetchForOrganisationByStatus')
            ->with($organisationId, EbsrSubmissionEntity::UPLOADED_STATUS)
            ->andReturn($searchResults);

        $this->assertEquals($expectedQueryResult, $this->sut->handleQuery($query));
    }

    /**
     * Tests handleQuery when no organisation exists
     */
    public function testHandleQueryNoOrganisation(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $query = Qry::create([]);

        $identity = m::mock(IdentityInterface::class);
        $identity->shouldReceive('getUser->getOrganisationUsers->isEmpty')->once()->andReturn(true);

        $this->mockedSmServices[AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->once()
            ->andReturn($identity);

        $this->sut->handleQuery($query);
    }
}
