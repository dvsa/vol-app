<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\Exists as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Licence as Repo;
use Dvsa\Olcs\Transfer\Query\Licence\Exists as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;

/**
 * ExistsTest
 *
 * @author Ian Lindsay <ian@hemera-business-services.co.uk>
 */
class ExistsTest extends QueryHandlerTestCase
{
    /**
     * set up test
     */
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', Repo::class);

        parent::setUp();
    }

    /**
     * tests handle query
     */
    public function testHandleQuery(): void
    {
        $licNo = 'PB2141421';
        $licenceExists = true;
        $expectedResult = ['isValid' => $licenceExists];
        $query = Query::create(['licNo' => $licNo]);
        $this->repoMap['Licence']->shouldReceive('existsByLicNo')->with($licNo)->andReturn($licenceExists);

        $this->assertEquals($expectedResult, $this->sut->handleQuery($query));
    }
}
