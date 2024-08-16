<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\BusRegSearchView;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\QueryHandler\BusRegSearchView\BusRegSearchViewList;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser as OrganisationUserEntity;
use Dvsa\Olcs\Api\Domain\Query\BusRegSearchView\BusRegSearchViewList as Qry;
use Dvsa\Olcs\Transfer\Query\BusRegSearchView\BusRegSearchViewList as BusRegSearchViewTransferQry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * @covers \Dvsa\Olcs\Api\Domain\QueryHandler\BusRegSearchView\BusRegSearchViewList
 */
class BusRegSearchViewListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new BusRegSearchViewList();
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
     * @return m\MockInterface
     */
    private function getCurrentUser($localAuthorityId = null, $organisationId = null)
    {
        /** @var \Dvsa\Olcs\Api\Entity\User\User|m\MockInterface $mockUser */
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

    /**
     * Test handle query for Operator users
     */
    public function testHandleQueryOperator()
    {
        $organisationId = 1;
        $localAuthorityId = null;

        $currentUser = $this->getCurrentUser($localAuthorityId, $organisationId);

        // check for operator
        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]->shouldReceive('isGranted')
            ->with(m::type('string'), null)->andReturn(true);

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($currentUser);

        $data = [
            'licId' => 1234,
            'busRegStatus' => 'breg_s_cancellation',
            'organisationId' => $organisationId,
            'localAuthorityId' => $localAuthorityId,
            'page' => 4,
            'limit' => 10,
            'sort' => 'licId',
            'order' => 'ASC'
        ];

        $query = Qry::create($data);

        $mockRecord = m::mock();
        $mockRecord->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $this->repoMap['BusRegSearchView']
            ->shouldReceive('fetchList')
            ->once()
            ->with(m::type(Qry::class), Query::HYDRATE_OBJECT)
            ->andReturn([$mockRecord])
            ->shouldReceive('fetchCount')
            ->once()
            ->with(m::type(Qry::class))
            ->andReturn(1);

        $expected = [
            'result' => [
                ['foo' => 'bar']
            ],
            'count' => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
        $this->assertNotEmpty($query->getOrganisationId());
        $this->assertEmpty($query->getLocalAuthorityId());
        $expectedData = $data;
        $expectedData['sortWhitelist'] = [];
        $this->assertEquals($expectedData, $query->getArrayCopy());
    }

    /**
     * Test handle query for LA users
     */
    public function testHandleQueryLocalAuthority()
    {
        $organisationId = null;
        $localAuthorityId = 1;
        $currentUser = $this->getCurrentUser($localAuthorityId, $organisationId);

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

        $data = [
            'licId' => 1234,
            'busRegStatus' => 'breg_s_cancellation',
            'organisationId' => $organisationId,
            'localAuthorityId' => $localAuthorityId,
            'page' => 4,
            'limit' => 10,
            'sort' => 'licId',
            'order' => 'ASC'
        ];
        $query = Qry::create($data);

        $mockRecord = m::mock();
        $mockRecord->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $this->repoMap['BusRegSearchView']
            ->shouldReceive('fetchList')
            ->once()
            ->with(m::type(Qry::class), Query::HYDRATE_OBJECT)
            ->andReturn([$mockRecord])
            ->shouldReceive('fetchCount')
            ->once()
            ->with(m::type(Qry::class))
            ->andReturn(1);

        $expected = [
            'result' => [
                ['foo' => 'bar']
            ],
            'count' => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
        $this->assertNotEmpty($query->getLocalAuthorityId());
        $this->assertEmpty($query->getOrganisationId());
        $expectedData = $data;
        $expectedData['sortWhitelist'] = [];
        $this->assertEquals($expectedData, $query->getArrayCopy());
    }

    /**
     * Test handle query converts query
     */
    public function testHandleQueryConversion()
    {
        $organisationId = null;
        $localAuthorityId = 1;
        $currentUser = $this->getCurrentUser($localAuthorityId, $organisationId);

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

        $data = [
            'organisationId' => 9999,
            'licId' => 1234,
            'busRegStatus' => 'breg_s_cancellation',
            'page' => 4,
            'limit' => 10,
            'sort' => 'licId',
            'order' => 'ASC',
        ];
        $query = BusRegSearchViewTransferQry::create($data);

        $mockRecord = m::mock();
        $mockRecord->shouldReceive('serialize')->andReturn(['foo' => 'bar']);

        $this->repoMap['BusRegSearchView']
            ->shouldReceive('fetchList')
            ->once()
            ->with(m::type(Qry::class), Query::HYDRATE_OBJECT)
            ->andReturn([$mockRecord])
            ->shouldReceive('fetchCount')
            ->once()
            ->with(m::type(Qry::class))
            ->andReturn(1);

        $expected = [
            'result' => [
                ['foo' => 'bar']
            ],
            'count' => 1
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query));
        $expectedData = $data;
        $expectedData['sortWhitelist'] = [];
        $this->assertEquals($expectedData, $query->getArrayCopy());
        $this->assertArrayNotHasKey('localAuthorityId', $query->getArrayCopy());
    }
}
