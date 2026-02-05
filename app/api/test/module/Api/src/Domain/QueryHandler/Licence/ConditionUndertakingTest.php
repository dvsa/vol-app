<?php

declare(strict_types=1);

/**
 * ConditionUndertaking Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\ConditionUndertaking as QueryHandler;
use Dvsa\Olcs\Transfer\Query\Licence\ConditionUndertaking as Qry;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;

/**
 * ConditionUndertaking Test
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class ConditionUndertakingTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $data = ['id' => 234];
        $query = Qry::create($data);

        $mockLicence = m::mock(LicenceEntity::class)
            ->shouldReceive('serialize')->with([])->once()->andReturn(['LIC'])->getMock();
        $mockCondUnder = m::mock()
            ->shouldReceive('serialize')->with(['attachedTo', 'conditionType', 'operatingCentre' => ['address']])
            ->once()->andReturn(['CONDITION'])->getMock();

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($query)->once()->andReturn($mockLicence);
        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchListForLicenceReadOnly')->with(234)->once()
            ->andReturn([$mockCondUnder]);

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['LIC', 'conditionUndertakings' => [['CONDITION']]], $result->serialize());
    }
}
