<?php

declare(strict_types=1);

/**
 * Recipient Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Publication;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Publication\Recipient;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Recipient as RecipientRepo;
use Dvsa\Olcs\Api\Domain\QueryHandler\BundleSerializableInterface;
use Dvsa\Olcs\Transfer\Query\Publication\Recipient as Qry;

/**
 * Recipient Test
 */
class RecipientTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Recipient();
        $this->mockRepo('Recipient', RecipientRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 1]);

        $this->repoMap['Recipient']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn(
                m::mock(BundleSerializableInterface::class)
                    ->shouldReceive('serialize')
                    ->andReturn(['foo'])
                    ->getMock()
            );

        $result = $this->sut->handleQuery($query);
        $this->assertEquals(['foo'], $result->serialize());
    }
}
