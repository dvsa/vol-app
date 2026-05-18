<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\Template\AvailableTemplateGroups;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Transfer\Query\Template\AvailableTemplateGroups as Qry;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

class AvailableTemplateGroupsTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new AvailableTemplateGroups();
        $this->mockRepo('Template', TemplateRepo::class);

        parent::setUp();
    }

    public function testHandleQueryReturnsGroupedResultPlusCount(): void
    {
        $query = Qry::create(['page' => 1, 'limit' => 25]);

        $groupedResult = [
            [
                'id' => 113,
                'name' => 'auth-forgot-password',
                'description' => 'Email template - password reset',
                'categoryName' => null,
                'locales' => ['cy_GB', 'en_GB'],
                'formats' => ['html', 'md', 'plain'],
            ],
        ];

        $this->repoMap['Template']
            ->shouldReceive('fetchTemplateGroups')->with($query)->once()->andReturn($groupedResult);
        $this->repoMap['Template']
            ->shouldReceive('countTemplateGroups')->with($query)->once()->andReturn(31);

        $result = $this->sut->handleQuery($query);

        $this->assertSame(['result' => $groupedResult, 'count' => 31], $result);
    }
}
