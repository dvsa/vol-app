<?php

declare(strict_types=1);

/**
 * BusRegWithTxcInboxTest
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus\Ebsr;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr\BusRegWithTxcInbox;
use Dvsa\Olcs\Api\Domain\Repository\Bus as BusRepo;
use Dvsa\Olcs\Api\Domain\Repository\TxcInbox as TxcInboxRepo;
use Dvsa\Olcs\Api\Entity\Bus\BusNoticePeriod;
use Dvsa\Olcs\Api\Entity\Bus\BusReg as BusRegEntity;
use Dvsa\Olcs\Api\Entity\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\BusRegWithTxcInbox as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * BusRegWithTxcInboxTest
 * Tests include for LA, operator and anonymous users querying bus regs that exist and dont exist
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class BusRegWithTxcInboxTest extends QueryHandlerTestCase
{
    /**
     * Set up repos and services
     */
    public function setUp(): void
    {
        $this->sut = new BusRegWithTxcInbox();
        $this->mockRepo('TxcInbox', TxcInboxRepo::class);
        $this->mockRepo('Bus', BusRepo::class);

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
    private function getCurrentUser(mixed $localAuthorityId = null, mixed $organisationId = null): m\MockInterface
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
            $organisation = new Organisation();
            $organisation->setId($organisationId);

            $organisationUser = new OrganisationUser();

            $organisationUser->setOrganisation($organisation);
            $organisationUsers->add($organisationUser);
        }
        $mockUser->setOrganisationUsers($organisationUsers);

        return $mockUser;
    }

    /**
     * Test operator user querying a bus reg
     */
    public function testHandleQueryForOrganisation(): void
    {
        $busRegId = 2;
        $organisationId = 6;
        $query = Qry::create(
            [
                'id' => $busRegId
            ]
        );

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($this->getCurrentUser(null, $organisationId));

        $status = m::mock(RefData::class)->makePartial();
        $status->setId(BusRegEntity::STATUS_REGISTERED);

        $mockResult = new BusRegEntity();

        $busRegNoticePeriod = new BusNoticePeriod();
        $busRegNoticePeriod->setId(BusNoticePeriod::NOTICE_PERIOD_OTHER);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getLatestBusVariation')->andReturn(null);
        $licence->shouldReceive('determineNpNumber')
            ->once()
            ->andReturn('4321');

        $mockResult->setBusNoticePeriod($busRegNoticePeriod);
        $mockResult->setLicence($licence);
        $mockResult->setStatus($status);

        $this->repoMap['Bus']->shouldReceive('fetchWithTxcInboxListForOrganisation')
            ->with($query, $organisationId)
            ->andReturn($mockResult);

        $this->sut->handleQuery($query);
    }

    /**
     * Test LA user querying a bus reg
     */
    public function testHandleQueryForLocalAuthority(): void
    {
        $busRegId = 2;
        $localAuthorityId = 4;
        $query = Qry::create(
            [
                'id' => $busRegId
            ]
        );

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($this->getCurrentUser($localAuthorityId));

        $status = m::mock(RefData::class)->makePartial();
        $status->setId(BusRegEntity::STATUS_REGISTERED);

        $mockLicence = m::mock();
        $mockLicence->shouldReceive('determineNpNumber')->andReturn('333');
        $mockLicence->shouldReceive('getLatestBusVariation')->andReturn(null);

        $mockResult = new BusRegEntity();

        $busRegNoticePeriod = new BusNoticePeriod();
        $busRegNoticePeriod->setId(BusNoticePeriod::NOTICE_PERIOD_OTHER);

        $mockResult->setLicence($mockLicence);
        $mockResult->setBusNoticePeriod($busRegNoticePeriod);
        $mockResult->setStatus($status);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getLatestBusVariation')->andReturnNull();
        $licence->shouldReceive('determineNpNumber')
            ->once()
            ->andReturn('4321');

        $mockResult->setLicence($licence);

        $this->repoMap['Bus']->shouldReceive('fetchWithTxcInboxListForLocalAuthority')
            ->with($query, $localAuthorityId)
            ->andReturn($mockResult);

        $this->sut->handleQuery($query);
    }

    /**
     * Test anon user querying a bus reg
     */
    public function testHandleQueryForAnonUser(): void
    {
        $busRegId = 2;
        $localAuthorityId = 4;
        $query = Qry::create(
            [
                'id' => $busRegId
            ]
        );

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($this->getCurrentUser());

        $status = m::mock(RefData::class)->makePartial();
        $status->setId(BusRegEntity::STATUS_REGISTERED);

        $mockLicence = m::mock();
        $mockLicence->shouldReceive('determineNpNumber')->andReturn('333');
        $mockLicence->shouldReceive('getLatestBusVariation')->andReturn(null);

        $mockResult = new BusRegEntity();

        $busRegNoticePeriod = new BusNoticePeriod();
        $busRegNoticePeriod->setId(BusNoticePeriod::NOTICE_PERIOD_OTHER);

        $mockResult->setLicence($mockLicence);
        $mockResult->setBusNoticePeriod($busRegNoticePeriod);
        $mockResult->setStatus($status);

        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('getLatestBusVariation')->andReturnNull();
        $licence->shouldReceive('determineNpNumber')
            ->once()
            ->andReturn('4321');

        $mockResult->setLicence($licence);

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockResult);

        $this->sut->handleQuery($query);
    }

    /**
     * Test LA user querying a non existent bus reg
     */
    public function testHandleQueryBusRegNotFoundException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $busRegId = 2;
        $query = Qry::create(
            [
                'id' => $busRegId
            ]
        );

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($this->getCurrentUser(4));

        $this->repoMap['Bus']->shouldReceive('fetchWithTxcInboxListForLocalAuthority')
            ->with($query, 4)
            ->andReturnNull();

        $this->repoMap['Bus']->shouldReceive('fetchById')
            ->with($query->getId())
            ->andReturnNull();

        $this->sut->handleQuery($query);
    }

    /**
     * Test Anon user querying a non existent bus reg
     */
    public function testHandleQueryForAnonBusRegNotFoundException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\NotFoundException::class);

        $busRegId = 2;
        $query = Qry::create(
            [
                'id' => $busRegId
            ]
        );

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity')
            ->andReturn($this->getCurrentUser());

        $this->repoMap['Bus']->shouldReceive('fetchWithTxcInboxListForLocalAuthority')
            ->with($query, 4)
            ->andReturnNull();

        $this->repoMap['Bus']->shouldReceive('fetchWithTxcInboxListForOrganisation')
            ->with($query, 4)
            ->andReturnNull();

        $this->repoMap['Bus']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturnNull();

        $this->sut->handleQuery($query);
    }
}
