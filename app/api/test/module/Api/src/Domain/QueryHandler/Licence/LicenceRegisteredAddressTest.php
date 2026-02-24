<?php

declare(strict_types=1);

/**
 * LicenceRegisteredAddress Test
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\LicenceRegisteredAddress as Sut;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Licence as LicenceRepo;
use Dvsa\Olcs\Transfer\Query\Licence\LicenceRegisteredAddress as Qry;

/**
 * LicenceRegisteredAddress Test
 */
class LicenceRegisteredAddressTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('Licence', LicenceRepo::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $licNo = 'licNo';

        $query = Qry::create(['licenceNumber' => $licNo]);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar']);

        $this->repoMap['Licence']->shouldReceive('fetchForUserRegistration')
            ->with($licNo)
            ->andReturn($licence);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
        ];

        $this->assertEquals($expected, $result->serialize());
    }
}
