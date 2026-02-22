<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Surrender;

use Dvsa\Olcs\Api\Domain\QueryHandler\Surrender\GetSignature as QryHandler;
use Dvsa\Olcs\Api\Domain\Repository\Licence;
use Dvsa\Olcs\Api\Domain\Repository\Surrender;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Transfer\Query\Surrender\GetSignature;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

class SignatureTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QryHandler();
        $this->mockRepo('Surrender', Surrender::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = GetSignature::create(['id' => 1]);
        $mockSurrender = m::mock(\Dvsa\Olcs\Api\Entity\Surrender::class);
        $mockSurrender->shouldReceive('serialize')->andReturn(['test']);
        $this->repoMap['Surrender']->
        shouldReceive('fetchOneByLicence')->once()->with(1, 1)->andReturn($mockSurrender);
        $result = $this->sut->handleQuery($query);
        $this->assertEquals(['test'], $result->serialize());
    }
}
