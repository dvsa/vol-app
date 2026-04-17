<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitWindow;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitWindow\OpenByCountry as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as PermitWindowRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as PermitWindowEntity;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\OpenByCountry as ListQuery;

/**
 * GetList Test
 */
class OpenByCountryTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('IrhpPermitWindow', PermitWindowRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $query = ListQuery::create(['countries' => ['DE', 'FR']]);

        $item1 = m::mock(PermitWindowEntity::class)->makePartial();
        $item2 = m::mock(PermitWindowEntity::class)->makePartial();
        $permitWindows = new ArrayCollection();

        $permitWindows->add($item1);
        $permitWindows->add($item2);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchOpenWindowsByCountry')
            ->once()
            ->andReturn($permitWindows);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'result' => [
                0 => [],
                1 => []
            ]
        ];

        $this->assertIsArray($result);

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $result);
            if (is_array($value)) {
                $this->assertIsArray($result[$key]);
                $this->assertCount(count($value), $result[$key]);
            } else {
                $this->assertEquals($value, $result[$key]);
            }
        }
    }
}
