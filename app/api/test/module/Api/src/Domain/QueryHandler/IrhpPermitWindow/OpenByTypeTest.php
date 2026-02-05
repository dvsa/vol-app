<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\IrhpPermitWindow;

use Doctrine\Common\Collections\ArrayCollection;
use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\IrhpPermitWindow\OpenByType as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\IrhpPermitWindow as PermitWindowRepo;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitWindow as PermitWindowEntity;
use Dvsa\Olcs\Transfer\Query\IrhpPermitWindow\OpenByType as ListQuery;

/**
 * Open By Type Test
 * @author Andy Newton <andy@vitri.ltd>
 */
class OpenByTypeTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('IrhpPermitWindow', PermitWindowRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $query = ListQuery::create(['irhpPermitType' => 5, 'currentDateTime' => '2020-05-01 10:10:20']);

        $item1 = m::mock(PermitWindowEntity::class)->makePartial();
        $item2 = m::mock(PermitWindowEntity::class)->makePartial();
        $permitWindows = new ArrayCollection();
        $permitWindows->add($item1);
        $permitWindows->add($item2);

        $this->repoMap['IrhpPermitWindow']
            ->shouldReceive('fetchOpenWindowsByType')
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
