<?php

declare(strict_types=1);

/**
 * Interim Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Application;

use Dvsa\Olcs\Api\Domain\QueryHandler\Application\Interim;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Application\Interim as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Interim Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class InterimTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Interim();
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('handlerQueryProvider')]
    public function testHandleQuery(mixed $status, mixed $isInterimRequested, mixed $isInterimInforce, mixed $canSetStatus, mixed $canUpdatedInterim): void
    {
        $query = Qry::create(['id' => 111]);

        $statusObj = m::mock(RefData::class)->makePartial();
        $statusObj->setId($status);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->shouldReceive('getFees')
            ->andReturn([]);
        $application->setInterimStatus($statusObj);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($application);

        $result = $this->sut->handleQuery($query);

        $data = $result->serialize();

        $this->assertEquals($isInterimRequested, $data['isInterimRequested']);
        $this->assertEquals($isInterimInforce, $data['isInterimInforce']);
        $this->assertEquals($canSetStatus, $data['canSetStatus']);
        $this->assertEquals($canUpdatedInterim, $data['canUpdateInterim']);
    }

    public static function handlerQueryProvider(): array
    {
        return [
            [
                ApplicationEntity::INTERIM_STATUS_REQUESTED,
                true,
                false,
                false,
                true
            ],
            [
                ApplicationEntity::INTERIM_STATUS_INFORCE,
                false,
                true,
                true,
                true
            ],
            [
                ApplicationEntity::INTERIM_STATUS_REFUSED,
                false,
                false,
                true,
                false
            ],
            [
                ApplicationEntity::INTERIM_STATUS_REVOKED,
                false,
                false,
                true,
                false
            ],
            [
                ApplicationEntity::INTERIM_STATUS_GRANTED,
                false,
                false,
                true,
                true
            ]
        ];
    }
}
