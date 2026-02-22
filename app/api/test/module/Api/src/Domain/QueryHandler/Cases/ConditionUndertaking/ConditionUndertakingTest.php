<?php

declare(strict_types=1);

/**
 * ConditionUndertaking Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\ConditionUndertaking;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\ConditionUndertaking\ConditionUndertaking;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;
use Dvsa\Olcs\Transfer\Query\Cases\ConditionUndertaking\ConditionUndertaking as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * ConditionUndertaking Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ConditionUndertakingTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new ConditionUndertaking();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);
        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);

        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchUsingCaseId')
            ->with($query)
            ->andReturn($mockResult);

        $result = $this->sut->handleQuery($query);

        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class, $result);
    }
}
