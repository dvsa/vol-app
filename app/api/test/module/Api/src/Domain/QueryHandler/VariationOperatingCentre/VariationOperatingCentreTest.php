<?php

declare(strict_types=1);

/**
 * Variation Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\VariationOperatingCentre;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\VariationOperatingCentre\VariationOperatingCentre;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\VariationOperatingCentre\VariationOperatingCentre as Qry;
use Dvsa\Olcs\Transfer\Query\ApplicationOperatingCentre\ApplicationOperatingCentre;
use Dvsa\Olcs\Transfer\Query\LicenceOperatingCentre\LicenceOperatingCentre;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\OlcsTest\Api\Entity\User as UserEntity;

/**
 * Variation Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class VariationOperatingCentreTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new VariationOperatingCentre();

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->shouldReceive('isAnonymous')->andReturn(false);
        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
                ->shouldReceive('isGranted')->andReturn(false)->getMock(),
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        $query = Qry::create(['id' => 'A111']);

        $response = m::mock();
        $response->shouldReceive('setValue')
            ->once()
            ->with('canUpdateAddress', false);
        $response->shouldReceive('serialize')->with()->once()->andReturn(['action' => 'U']);

        $this->queryHandler->shouldReceive('handleQuery')
            ->andReturnUsing(
                function ($dto) use ($response) {

                    $this->assertInstanceOf(ApplicationOperatingCentre::class, $dto);
                    $data = $dto->getArrayCopy();

                    $this->assertEquals(111, $data['id']);

                    return $response;
                }
            );

        $this->assertEquals($response, $this->sut->handleQuery($query));
    }

    public function testHandleQueryAddedOperatingCentre(): void
    {
        $query = Qry::create(['id' => 'A111']);

        $response = m::mock();
        $response->shouldReceive('setValue')
            ->once()
            ->with('canUpdateAddress', true);
        $response->shouldReceive('serialize')->with()->once()->andReturn(['action' => 'A']);

        $this->queryHandler->shouldReceive('handleQuery')
            ->andReturnUsing(
                function ($dto) use ($response) {

                    $this->assertInstanceOf(ApplicationOperatingCentre::class, $dto);
                    $data = $dto->getArrayCopy();

                    $this->assertEquals(111, $data['id']);

                    return $response;
                }
            );

        $this->assertEquals($response, $this->sut->handleQuery($query));
    }

    public function testHandleQueryLicence(): void
    {
        $query = Qry::create(['id' => 'L111']);

        $response = m::mock();
        $response->shouldReceive('setValue')
            ->once()
            ->with('canUpdateAddress', false);

        $this->queryHandler->shouldReceive('handleQuery')
            ->andReturnUsing(
                function ($dto) use ($response) {

                    $this->assertInstanceOf(LicenceOperatingCentre::class, $dto);
                    $data = $dto->getArrayCopy();

                    $this->assertEquals(111, $data['id']);

                    return $response;
                }
            );

        $this->assertEquals($response, $this->sut->handleQuery($query));
    }

    public function testHandleQueryNeither(): void
    {
        $this->expectException(\Exception::class);

        $query = Qry::create(['id' => 'X111']);

        $this->assertEquals('RESPONSE', $this->sut->handleQuery($query));
    }
}
