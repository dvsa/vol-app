<?php

declare(strict_types=1);

/**
 * Unlicensed Cases Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Organisation;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\Organisation\UnlicensedCases;
use Dvsa\Olcs\Api\Domain\Repository\Cases as CasesRepo;
use Dvsa\Olcs\Api\Domain\Repository\Organisation as OrganisationRepo;
use Dvsa\Olcs\Api\Entity\Cases\Cases as CaseEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Transfer\Query\Organisation\UnlicensedCases as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\OlcsTest\Api\Entity\User as UserEntity;

/**
 * Unlicensed Cases Test
 *
 * @author Dan Eggleston <dan@stolenegg.com>
 */
class UnlicensedCasesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new UnlicensedCases();
        $this->mockRepo('Organisation', OrganisationRepo::class);
        $this->mockRepo('Cases', CasesRepo::class);

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->shouldReceive('isAnonymous')->andReturn(false);
        $currentUser->shouldReceive('isSoleTrader')->andReturn(false);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
                ->shouldReceive('isGranted')->andReturn(false)->getMock(),
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $organisationId = 69;
        $licenceId = 7;

        $query = Qry::create(
            [
                'id' => $organisationId,
                'page' => 1,
                'limit' => 10,
                'sort' => 'id',
                'order' => 'ASC',
            ]
        );

        $mockOrganisation = m::mock(OrganisationEntity::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();
        $mockOrganisation->shouldReceive('getLicences->first->getId')->andReturn($licenceId);
        $mockOrganisation->shouldReceive('getCalculatedBundleValues')->andReturn([]);
        $mockOrganisation->shouldReceive('serialize')->andReturn(['id' => $organisationId]);

        $this->repoMap['Organisation']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockOrganisation);

        $this->repoMap['Cases']->shouldReceive('fetchList')
            ->with(
                m::on(
                    function ($query) use ($licenceId) {
                        $this->assertEquals($licenceId, $query->getLicence());
                        $this->assertEquals(1, $query->getPage());
                        $this->assertEquals(10, $query->getLimit());
                        $this->assertEquals('id', $query->getSort());
                        $this->assertEquals('ASC', $query->getOrder());
                        return true;
                    }
                ),
                Query::HYDRATE_OBJECT
            )
            ->once()
            ->andReturn(
                [
                    m::mock(CaseEntity::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['id' => 1])
                        ->getMock(),
                    m::mock(CaseEntity::class)
                        ->shouldReceive('serialize')
                        ->andReturn(['id' => 2])
                        ->getMock(),
                ]
            )
            ->shouldReceive('fetchCount')
            ->once()
            ->andReturn(1);

        $expected = [
            'id' => $organisationId,
            'cases' => [
                'result' => [
                    ['id' => 1],
                    ['id' => 2],
                ],
                'count' => 1,
            ],
            'licenceId' => $licenceId,
        ];

        $result = $this->sut->handleQuery($query);

        $this->assertEquals($expected, $result->serialize());
    }
}
