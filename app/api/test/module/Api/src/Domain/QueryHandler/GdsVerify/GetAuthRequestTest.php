<?php

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\GdsVerify;

use Dvsa\Olcs\Api\Domain\QueryHandler\GdsVerify\GetAuthRequest;
use Dvsa\Olcs\GdsVerify\Service;
use Dvsa\Olcs\Transfer\Query\GdsVerify\GetAuthRequest as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Mockery as m;

/**
 * GetAuthRequest Test
 */
class GetAuthRequestTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new GetAuthRequest();
        $this->mockRepo('SystemParameter', \Dvsa\Olcs\Api\Domain\Repository\SystemParameter::class);
        $this->mockedSmServices[Service\GdsVerify::class] = m::mock(Service\GdsVerify::class);

        parent::setUp();
    }

    public function testHandleQueryVerifyDisabled()
    {
        $query = Query::create([]);
        $this->repoMap['SystemParameter']->shouldReceive('getDisableGdsVerifySignatures')
            ->with()->once()->andReturn(true);

        $actual = $this->sut->handleQuery($query);
        $this->assertSame(['enabled' => false], $actual);
    }

    public function testHandleQuery()
    {
        $query = Query::create([]);
        $this->repoMap['SystemParameter']->shouldReceive('getDisableGdsVerifySignatures')
            ->with()->once()->andReturn(false);
        $this->mockedSmServices[Service\GdsVerify::class]->shouldReceive('getAuthenticationRequest')
            ->with()->once()->andReturn(['foo' => 'bar']);

        $actual = $this->sut->handleQuery($query);
        $this->assertSame(
            ['foo' => 'bar', 'enabled' => true],
            $actual
        );
    }

    public function testSetGetGdsVerifyService()
    {
        $sut = new GetAuthRequest();
        $this->assertNull($sut->getGdsVerifyService());
        $mock = m::mock(Service\GdsVerify::class);
        $sut->setGdsVerifyService($mock);
        $this->assertSame($mock, $sut->getGdsVerifyService());
    }
}
