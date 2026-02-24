<?php

declare(strict_types=1);

/**
 * EbsrSubmissionListList Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Bus\Ebsr;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Query\Bus\EbsrSubmissionList as ListDto;
use Dvsa\Olcs\Api\Domain\QueryHandler\Bus\Ebsr\EbsrSubmissionList;
use Dvsa\Olcs\Api\Domain\Repository\EbsrSubmission as EbsrSubmissionRepo;
use Dvsa\Olcs\Api\Entity\Ebsr\EbsrSubmission as EbsrSubmissionEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\Organisation\OrganisationUser;
use Dvsa\Olcs\Transfer\Query\Bus\Ebsr\EbsrSubmissionList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * EbsrSubmissionListTest
 */
class EbsrSubmissionListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EbsrSubmissionList();
        $this->mockRepo('EbsrSubmission', EbsrSubmissionRepo::class);

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
     * test handle query
     *
     *
     * @param string $status         initial search status
     * @param array  $mappedStatuses array of mapped statuses expected
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('queryStatusProvider')]
    public function testHandleQuery(mixed $status, mixed $mappedStatuses): void
    {
        $query = Qry::create(['status' => $status]);

        $resultCount = 2;
        $organisationId = 5;

        $this->mockedSmServices[\LmcRbacMvc\Service\AuthorizationService::class]
            ->shouldReceive('getIdentity->getUser')
            ->andReturn($this->getCurrentUser(null, $organisationId));

        $mockEbsrSubmission = m::mock(EbsrSubmissionEntity::class);
        $mockEbsrSubmission->shouldReceive('serialize')->once();

        $this->repoMap['EbsrSubmission']->shouldReceive('fetchList')
            ->with(m::type(ListDto::class), Query::HYDRATE_OBJECT)
            ->andReturn([$mockEbsrSubmission]);

        $this->repoMap['EbsrSubmission']->shouldReceive('fetchCount')
            ->with(m::type(ListDto::class))
            ->andReturn($resultCount);

        $result = $this->sut->handleQuery($query);
        $this->assertCount(2, $result);
        $this->assertEquals($resultCount, $result['count']);

        $fetchListVars = $this->sut->getListDto()->getArrayCopy();

        $this->assertEquals($organisationId, $fetchListVars['organisation']);
        $this->assertEquals($mappedStatuses, $fetchListVars['status']);
    }

    /**
     * Data provider for testHandleQuery
     *
     * @return array
     */
    public static function queryStatusProvider(): array
    {
        return [
            [
                EbsrSubmissionEntity::FAILED_DISPLAY_TYPE,
                EbsrSubmissionEntity::$displayStatus[EbsrSubmissionEntity::FAILED_DISPLAY_TYPE]
            ],
            [
                EbsrSubmissionEntity::PROCESSING_DISPLAY_TYPE,
                EbsrSubmissionEntity::$displayStatus[EbsrSubmissionEntity::PROCESSING_DISPLAY_TYPE]
            ],
            [
                EbsrSubmissionEntity::PROCESSED_DISPLAY_TYPE,
                EbsrSubmissionEntity::$displayStatus[EbsrSubmissionEntity::PROCESSED_DISPLAY_TYPE]
            ],
            [
                null,
                EbsrSubmissionEntity::$displayStatus['all_valid']
            ]
        ];
    }
}
