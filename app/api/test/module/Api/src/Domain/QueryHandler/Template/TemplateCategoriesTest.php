<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Template;

use Dvsa\Olcs\Api\Domain\QueryHandler\Template\TemplateCategories as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Template as TemplateRepo;
use Dvsa\Olcs\Transfer\Query\Template\TemplateCategories as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * TemplateCategories Test
 */
class TemplateCategoriesTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Template', TemplateRepo::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $query = Query::create([]);

        $results = [
            ['id' => 1],
            ['id' => 2],
        ];

        $this->repoMap['Template']
            ->shouldReceive('fetchDistinctCategories')
            ->withNoArgs()
            ->once()
            ->andReturn($results);

        self::assertEquals(
            [
                'result' => $results,
                'count' => 2,
            ],
            $this->sut->handleQuery($query)
        );
    }
}
