<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\ConditionUndertaking;

use Mockery as m;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\QueryHandler\ConditionUndertaking\GetList as QueryHandler;
use Dvsa\Olcs\Transfer\Query\ConditionUndertaking\GetList as Qry;
use Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking as ConditionUndertakingEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Api\Domain\Repository\ConditionUndertaking as ConditionUndertakingRepo;

/**
 * GetListTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class GetListTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new QueryHandler();
        $this->mockRepo('ConditionUndertaking', ConditionUndertakingRepo::class);
        $this->mockRepo('Application', ApplicationRepo::class);

        parent::setUp();
    }

    public function testHandleQueryNoLicenceNoApplication(): void
    {
        $data = [];
        $query = Qry::create($data);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleQuery($query);
    }

    public function testHandleQueryLicenceAndApplication(): void
    {
        $data = ['application' => 234, 'licence' => 237];
        $query = Qry::create($data);

        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $this->sut->handleQuery($query);
    }

    public function testHandleQueryApplication(): void
    {
        $data = ['application' => 234];
        $query = Qry::create($data);

        $mockApplication = new \Dvsa\Olcs\Api\Entity\Application\Application(
            m::mock(LicenceEntity::class),
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            0
        );
        $mockApplication->setId(234);
        $mockConditionUndertaking = new \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking(
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            1,
            1
        );
        $mockConditionUndertaking->setId(324);

        $this->repoMap['Application']->shouldReceive('fetchById')->with(234)->once()->andReturn($mockApplication);
        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchListForApplication')->with(234)->once()
            ->andReturn([$mockConditionUndertaking]);

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertIsArray($result['result']);

        $this->assertNotEmpty($result['result']);
        $this->assertArrayHasKey(0, $result['result']);

        $this->assertIsArray($result['result'][0]);
        $this->assertArrayHasKey('id', $result['result'][0]);
        $this->assertSame(324, $result['result'][0]['id']);

        $this->assertArrayHasKey('count', $result);
        $this->assertSame(1, $result['count']);
    }

    public function testHandleQueryVariation(): void
    {
        $data = ['application' => 234];
        $query = Qry::create($data);

        $mockLicence = new LicenceEntity(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $mockLicence->setId(54);
        $mockApplication = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $mockLicence,
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            1
        );
        $mockApplication->setId(234);
        $mockConditionUndertaking = new \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking(
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            1,
            1
        );
        $mockConditionUndertaking->setId(324);

        $this->repoMap['Application']->shouldReceive('fetchById')->with(234)->once()->andReturn($mockApplication);
        $this->repoMap['ConditionUndertaking']->shouldReceive('fetchListForVariation')->with(234, 54)->once()
            ->andReturn([$mockConditionUndertaking]);

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertIsArray($result['result']);

        $this->assertNotEmpty($result['result']);
        $this->assertArrayHasKey(0, $result['result']);

        $this->assertIsArray($result['result'][0]);
        $this->assertArrayHasKey('id', $result['result'][0]);
        $this->assertSame(324, $result['result'][0]['id']);

        $this->assertArrayHasKey('count', $result);
        $this->assertSame(1, $result['count']);
    }

    public function testHandleQueryLicence(): void
    {
        $data = ['licence' => 54, 'conditionType' => ConditionUndertakingEntity::TYPE_CONDITION];
        $query = Qry::create($data);

        $mockLicence = new LicenceEntity(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            new \Dvsa\Olcs\Api\Entity\System\RefData()
        );
        $mockLicence->setId(54);
        $mockConditionUndertaking = new \Dvsa\Olcs\Api\Entity\Cases\ConditionUndertaking(
            new \Dvsa\Olcs\Api\Entity\System\RefData(),
            1,
            1
        );
        $mockConditionUndertaking->setId(324);

        $this->repoMap['ConditionUndertaking']
            ->shouldReceive('fetchListForLicence')
            ->once()
            ->with(54, ConditionUndertakingEntity::TYPE_CONDITION)
            ->andReturn([$mockConditionUndertaking]);

        $result = $this->sut->handleQuery($query);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('result', $result);
        $this->assertIsArray($result['result']);

        $this->assertNotEmpty($result['result']);
        $this->assertArrayHasKey(0, $result['result']);

        $this->assertIsArray($result['result'][0]);
        $this->assertArrayHasKey('id', $result['result'][0]);
        $this->assertSame(324, $result['result'][0]['id']);

        $this->assertArrayHasKey('count', $result);
        $this->assertSame(1, $result['count']);
    }
}
