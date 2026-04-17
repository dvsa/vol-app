<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Domain\Exception\NotFoundException;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\ExistsWithOperatorAdmin as QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Licence as Repo;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Transfer\Query\Licence\ExistsWithOperatorAdmin as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class ExistsWithOperatorAdminTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('Licence', Repo::class);

        parent::setUp();
    }

    public function testHandleQueryNotFound(): void
    {
        $licNo = 'PB2141421';
        $query = Query::create(['licNo' => $licNo]);

        $this->repoMap['Licence']->expects('fetchByLicNoWithoutAdditionalData')->with($licNo)->andThrow(NotFoundException::class);

        $expectedResult = [
            'licenceExists' => false,
            'hasOperatorAdmin' => false,
        ];

        $this->assertEquals($expectedResult, $this->sut->handleQuery($query));
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery')]
    public function testHandleQuery(bool $isOperatorAdmin): void
    {
        $licNo = 'PB2141421';
        $query = Query::create(['licNo' => $licNo]);

        $organisation = m::mock(Organisation::class);
        $organisation->expects('hasOperatorAdmin')->andReturn($isOperatorAdmin);

        $licence = m::mock(LicenceEntity::class);
        $licence->expects('getOrganisation')->andReturn($organisation);

        $this->repoMap['Licence']->expects('fetchByLicNoWithoutAdditionalData')->with($licNo)->andReturn($licence);

        $expectedResult = [
            'licenceExists' => true,
            'hasOperatorAdmin' => $isOperatorAdmin,
        ];

        $this->assertEquals($expectedResult, $this->sut->handleQuery($query));
    }

    public static function dpHandleQuery(): array
    {
        return [
            [true],
            [false],
        ];
    }
}
