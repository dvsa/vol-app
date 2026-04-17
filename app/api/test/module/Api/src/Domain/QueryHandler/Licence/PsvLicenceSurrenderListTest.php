<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\PsvLicenceSurrenderList;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Api\Domain\Repository\Task as TaskRepo;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\System\SubCategory;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Domain\Query\Licence\PsvLicenceSurrenderList as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;
use Mockery as m;

class PsvLicenceSurrenderListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new PsvLicenceSurrenderList();

        $this->mockRepo('Licence', LicenceRepo::class);
        $this->mockRepo('Task', TaskRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $date = new DateTime('now');
        $query = Qry::create(['date' => $date]);

        $this->repoMap['Licence']
            ->shouldReceive('fetchPsvLicenceIdsToSurrender')
            ->with($date)
            ->andReturn([1, 2]);

        $this->repoMap['Task']
            ->shouldReceive('fetchOpenedTasksForLicences')
            ->with(
                [1, 2],
                Category::CATEGORY_LICENSING,
                SubCategory::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
                Task::TASK_DESCRIPTION_LICENCE_EXPIRED
            )
            ->andReturn(
                [
                    ['id' => 100, 'licence' => ['id' => 1]],
                    ['id' => 200, 'licence' => ['id' => 10]],
                ]
            );

        $result = $this->sut->handleQuery($query);

        $this->assertEquals(['result' => [1 => 2], 'count' => 1], $result);
    }
}
