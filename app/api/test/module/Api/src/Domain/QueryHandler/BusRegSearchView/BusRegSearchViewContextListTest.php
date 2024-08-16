<?php

/**
 * BusRegSearchViewContextList Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\BusRegSearchView;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\BusRegSearchView\BusRegSearchViewContextList;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewContextList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as OrganisationUserEntity;

/**
 * BusRegSearchViewContextList Test
 */
class BusRegSearchViewContextListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusRegSearchViewContextList();
        $this->mockRepo('BusRegSearchView', Repository\BusRegSearchView::class);
        $this->mockedSmServices = [
            \LmcRbacMvc\Service\AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class)
        ];
        parent::setUp();
    }

    /**
     * Set up a user for testing
     *
     * @param null $localAuthorityId
     * @param null $organisationId
     * @return m\Mock
     */
    private function getCurrentUser($localAuthorityId = null, $organisationId = null)
    {
        $mockUser = m::mock(\Dvsa\Olcs\Api\Entity\User\User::class)->makePartial();
        $mockUser->shouldReceive('getUser')
            ->andReturnSelf();

        if (!empty($localAuthorityId)) {
            $localAuthority = new \Dvsa\Olcs\Api\Entity\Bus\LocalAuthority();
            $localAuthority->setId($localAuthorityId);
        } else {
            $localAuthority = null;
        }
        $mockUser->setLocalAuthority($localAuthority);

        $organisationUsers = new ArrayCollection();

        if (!empty($organisationId)) {
            $organisation = new OrganisationEntity();
            $organisation->setId($organisationId);

            $organisationUser = new OrganisationUserEntity();

            $organisationUser->setOrganisation($organisation);
            $organisationUsers->add($organisationUser);
        }
        $mockUser->setOrganisationUsers($organisationUsers);

        return $mockUser;
    }

    public function testHandleQueryOperator()
    {
        $organisationId = 1;
        $localAuthorityId = null;

        $currentUser = $this->getCurrentUser(null, $organisationId);

        // check for operator
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]->shouldReceive('isGranted')
            ->with(m::type('string'), null)->andReturn(true);

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($currentUser);

        $query = Qry::create(
            [
                'context' => 'foo'
            ]
        );

        $mockRecord = ['foo' => 'bar'];

        $this->repoMap['BusRegSearchView']
            ->shouldReceive('fetchDistinctList')
            ->once()
            ->with($query, $organisationId, $localAuthorityId)
            ->andReturn([$mockRecord]);

        $expected = [
            'result' => [
               ['foo' => 'bar']
            ],
            'count' => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }

    public function testHandleQueryLocalAuthority()
    {
        $organisationId = null;
        $localAuthorityId = 99;

        $currentUser = $this->getCurrentUser($localAuthorityId, null);

        // checks for operator before local authority so we mock these first
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]->shouldReceive('isGranted')
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::OPERATOR_ADMIN, null)->andReturn(false);
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]->shouldReceive('isGranted')
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::OPERATOR_USER, null)->andReturn(false);
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]->shouldReceive('isGranted')
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::LOCAL_AUTHORITY_USER, null)->andReturn(true);
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]->shouldReceive('isGranted')
            ->with(\Dvsa\Olcs\Api\Entity\User\Permission::LOCAL_AUTHORITY_ADMIN, null)->andReturn(true);

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($currentUser);

        $query = Qry::create(
            [
                'context' => 'foo'
            ]
        );

        $mockRecord = ['foo' => 'bar'];

        $this->repoMap['BusRegSearchView']
            ->shouldReceive('fetchDistinctList')
            ->once()
            ->with($query, $organisationId, $localAuthorityId)
            ->andReturn([$mockRecord]);

        $expected = [
            'result' => [
                ['foo' => 'bar']
            ],
            'count' => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
    }
}
