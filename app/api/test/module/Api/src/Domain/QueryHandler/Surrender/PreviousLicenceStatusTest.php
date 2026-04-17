<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Surrender;

use Dvsa\Olcs\Api\Domain\QueryHandler\Surrender\PreviousLicenceStatus as QryHandler;
use Dvsa\Olcs\Api\Domain\Repository\EventHistory;
use Dvsa\Olcs\Transfer\Query\Surrender\PreviousLicenceStatus as PreviousLicenceStatusQuery;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class PreviousLicenceStatusTest extends QueryHandlerTestCase
{
    public const LICENCE_STATUS = 'lic_sts';

    public function setUp(): void
    {
        $this->sut = new QryHandler();
        $this->mockRepo('EventHistory', EventHistory::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = PreviousLicenceStatusQuery::create(['id' => 1]);
        $this->repoMap['EventHistory']->shouldReceive('fetchPreviousLicenceStatus')
            ->once()->with(1)->andReturn(['status' => static::LICENCE_STATUS]);

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(['status' => static::LICENCE_STATUS], $result);
    }
}
