<?php

declare(strict_types=1);

/**
 * Type Of Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Variation;

use Dvsa\Olcs\Api\Entity\Application\Application;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Variation\TypeOfLicence;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository\Application as ApplicationRepo;
use Dvsa\Olcs\Transfer\Query\Variation\TypeOfLicence as Qry;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Type Of Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class TypeOfLicenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new TypeOfLicence();
        $this->mockRepo('Application', ApplicationRepo::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQuery(): void
    {
        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('canBecomeSpecialRestricted')
            ->once()
            ->andReturn(true);

        $licence->shouldReceive('getLicenceType->getId')
            ->withNoArgs()
            ->andReturn('curLicType');
        $licence->shouldReceive('getVehicleType->getId')
            ->withNoArgs()
            ->andReturn('curVehType');

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial()
            ->shouldReceive('serialize')
            ->once()
            ->andReturn(['foo' => 'bar'])
            ->getMock();

        $application->setLicence($licence);

        $query = Qry::create(['id' => 111]);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->once()
            ->with($query)
            ->andReturn($application);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::CAN_UPDATE_LICENCE_LICENCE_TYPE, $licence)
            ->andReturn(true);

        $expected = [
            'foo' => 'bar',
            'canBecomeSpecialRestricted' => true,
            'canUpdateLicenceType' => true,
            'currentLicenceType' => 'curLicType',
            'currentVehicleType' => 'curVehType',
        ];

        $result = $this->sut->handleQuery($query);
        $this->assertEquals($expected, $result->serialize());
    }
}
