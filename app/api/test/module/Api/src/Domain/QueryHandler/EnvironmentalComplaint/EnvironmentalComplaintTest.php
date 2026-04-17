<?php

declare(strict_types=1);

/**
 * EnvironmentalComplaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\EnvironmentalComplaint;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\EnvironmentalComplaint\EnvironmentalComplaint;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Complaint as ComplaintRepo;
use Dvsa\Olcs\Transfer\Query\EnvironmentalComplaint\EnvironmentalComplaint as Qry;

/**
 * EnvironmentalComplaint Test
 *
 * @author Shaun Lizzio <shaun@lizzio.co.uk>
 */
class EnvironmentalComplaintTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new EnvironmentalComplaint();
        $this->mockRepo('Complaint', ComplaintRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $mockResult = m::mock(\Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface::class);

        $this->repoMap['Complaint']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($mockResult);

        $result = $this->sut->handleQuery($query);
        $this->assertInstanceOf(\Dvsa\Olcs\Api\Domain\QueryHandler\Result::class, $result);
    }
}
