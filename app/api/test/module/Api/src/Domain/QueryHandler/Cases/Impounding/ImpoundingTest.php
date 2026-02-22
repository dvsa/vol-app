<?php

declare(strict_types=1);

/**
 * Impounding Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Cases\Impounding;

use Dvsa\Olcs\Api\Domain\QueryHandler\Cases\Impounding\Impounding;
use Dvsa\Olcs\Api\Domain\Repository\Impounding as ImpoundingRepo;
use Dvsa\Olcs\Transfer\Query\Cases\Impounding\Impounding as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * Impounding Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class ImpoundingTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Impounding();
        $this->mockRepo('Impounding', ImpoundingRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);

        $this->repoMap['Impounding']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockResult);

        $result = $this->sut->handleQuery($query);
        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class, $result);
    }
}
