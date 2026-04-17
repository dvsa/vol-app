<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\TransportManagerApplication;

use Dvsa\Olcs\Api\Domain\QueryHandler;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\Tm\TmQualification;
use Dvsa\Olcs\Api\Entity\Tm\TransportManager;
use Dvsa\Olcs\Api\Entity\Tm\TransportManagerApplication;
use Dvsa\Olcs\Transfer\Query\TransportManagerApplication\GetDetails as Query;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use LmcRbacMvc\Service\AuthorizationService;
use Mockery as m;

/**
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\QueryHandler\TransportManagerApplication\GetDetails::class)]
class GetDetailsTest extends QueryHandlerTestCase
{
    /** @var  QueryHandler\TransportManagerApplication\GetDetails  */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = new QueryHandler\TransportManagerApplication\GetDetails();

        $this->mockRepo('TransportManagerApplication', Repository\TransportManagerApplication::class);
        $this->mockRepo('ApplicationOperatingCentre', Repository\ApplicationOperatingCentre::class);
        $this->mockRepo('LicenceOperatingCentre', Repository\LicenceOperatingCentre::class);
        $this->mockRepo('PreviousConviction', Repository\PreviousConviction::class);
        $this->mockRepo('OtherLicence', Repository\OtherLicence::class);
        $this->mockRepo('TmEmployment', Repository\TmEmployment::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(\LmcRbacMvc\Service\AuthorizationService::class),
        ];

        parent::setUp();
    }

    public static function dpHandleQuery(): array
    {
        $lgvArQualification = m::mock(TmQualification::class);
        $lgvArQualification->shouldReceive('getSerialNo')
            ->withNoArgs()
            ->andReturn('ABC1234');

        return [
            'with LGV AR qualification' => [
                'lgvArQualification' => $lgvArQualification,
                'expectedLgvAcquiredRightsReferenceNumber' => 'ABC1234',
            ],
            'without LGV AR qualification' => [
                'lgvArQualification' => null,
                'expectedLgvAcquiredRightsReferenceNumber' => '',
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleQuery')]
    public function testHandleQuery(mixed $lgvArQualification, mixed $expectedLgvAcquiredRightsReferenceNumber): void
    {
        $query = Query::create(['id' => 32]);

        $licence = new \Dvsa\Olcs\Api\Entity\Licence\Licence(
            m::mock(\Dvsa\Olcs\Api\Entity\Organisation\Organisation::class),
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class)
        );
        $licence->setId(653);
        $application = new \Dvsa\Olcs\Api\Entity\Application\Application(
            $licence,
            m::mock(\Dvsa\Olcs\Api\Entity\System\RefData::class),
            false
        );
        $application->setId(53);

        $tm = m::mock(TransportManager::class)->makePartial();
        $tm->setId(213);
        $tm->shouldReceive('getLgvAcquiredRightsQualification')
            ->withNoArgs()
            ->once()
            ->andReturn($lgvArQualification);

        $tmaOl = new \Dvsa\Olcs\Api\Entity\OtherLicence\OtherLicence();

        $tma = m::mock(TransportManagerApplication::class)->makePartial();
        $tma->shouldReceive('serialize')->andReturn(['foo' => 'bar']);
        $tma->setApplication($application);
        $tma->setTransportManager($tm);
        $tma->setOtherLicences([$tmaOl]);

        $mockUser = m::mock()->shouldReceive('getTransportManager')->andReturn($tm)->getMock();

        $this->repoMap['TransportManagerApplication']->shouldReceive('fetchDetails')->with(32)->once()->andReturn($tma);

        // loadApplicationOperatingCentres
        $this->repoMap['ApplicationOperatingCentre']->shouldReceive('fetchByApplication')->with(53)->once();

        // loadLicenceOperatingCentres
        $this->repoMap['LicenceOperatingCentre']->shouldReceive('fetchByLicence')->with(653)->once();

        // loadTransportManagerPreviousConvictions
        $this->repoMap['PreviousConviction']->shouldReceive('fetchByTransportManager')->with(213)->once();

        // loadTransportManagerOtherLicences
        $this->repoMap['OtherLicence']->shouldReceive('fetchByTransportManager')->with(213)->once();

        // loadTransportManagerEmployements
        $this->repoMap['TmEmployment']->shouldReceive('fetchByTransportManager')->with(213)->once();

        $this->repoMap['SystemParameter']
            ->shouldReceive('getDisableGdsVerifySignatures')
            ->once()
            ->andReturn('disable gds verify signatures value');

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $expected = [
            'foo' => 'bar',
            'isTmLoggedInUser' => true,
            'disableSignatures' => 'disable gds verify signatures value',
            'lgvAcquiredRightsReferenceNumber' => $expectedLgvAcquiredRightsReferenceNumber,
        ];

        $this->assertEquals($expected, $this->sut->handleQuery($query)->serialize());
    }
}
