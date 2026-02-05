<?php

declare(strict_types=1);

/**
 * Appeal Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Hearing;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Hearing\Appeal;
use Dvsa\Olcs\Api\Domain\Repository\Appeal as AppealRepo;
use Dvsa\Olcs\Transfer\Query\Cases\Hearing\Appeal as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Appeal Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class AppealTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Appeal();
        $this->mockRepo('Appeal', AppealRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);

        $this->repoMap['Appeal']->shouldReceive('fetchUsingCaseId')
            ->with($query)
            ->andReturn($mockResult);

        $result = $this->sut->handleQuery($query);
        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class, $result);
    }
}
