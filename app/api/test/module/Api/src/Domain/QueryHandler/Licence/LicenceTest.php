<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\Licence;

use DMS\PHPUnitExtensions\ArraySubset\Assert;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Entity\User\User as UserEntity;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\Licence\Licence;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Query\Licence\Licence as Qry;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceTest extends QueryHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Licence();
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('LicenceVehicle', Repository\LicenceVehicle::class);
        $this->mockRepo('ContinuationDetail', Repository\ContinuationDetail::class);
        $this->mockRepo('Note', Repository\Note::class);
        $this->mockRepo('SystemParameter', Repository\SystemParameter::class);
        $this->mockRepo('Application', Repository\SystemParameter::class);

        /** @var UserEntity $currentUser */
        $currentUser = m::mock(UserEntity::class)->makePartial();
        $currentUser->shouldReceive('isAnonymous')->andReturn(false);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchActiveVehicleCount')
            ->with(111)
            ->andReturn(1);

        $this->repoMap['LicenceVehicle']->shouldReceive('fetchAllVehiclesCount')
            ->with(111)
            ->andReturn(1);

        $this->mockedSmServices = [
            'SectionAccessService' => m::mock(),
            AuthorizationService::class => m::mock(AuthorizationService::class)
                ->shouldReceive('isGranted')->andReturn(false)->getMock(),
        ];

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($currentUser);

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dptestHandleQuery')]
    public function testHandleQuery(mixed $isLicenceSurrenderAllowed, mixed $openApplicationsForLicence): void
    {
        $query = Qry::create(['id' => 111]);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);

        $licence->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getNiFlag')
            ->andReturn('N')
            ->once()
            ->getMock()
            ->shouldReceive('getOrganisation')->andReturn(
                m::mock(Organisation::class)->shouldReceive('isMlh')->once()
                    ->andReturn(true)
                    ->getMock()
            )
            ->shouldReceive('isSpecialRestricted')
            ->andReturn(false)
            ->once()
            ->shouldReceive('getStatus->getId')
            ->andReturn(LicenceEntity::LICENCE_STATUS_VALID);

        $mockContinuationDetail = m::mock(\Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail::class)
            ->shouldReceive('serialize')->with(['continuation', 'licence'])->once()->andReturn(['CD'])
            ->getMock();
        $this->repoMap['ContinuationDetail']->shouldReceive('fetchForLicence')->with(111)
            ->andReturn([$mockContinuationDetail]);
        $this->repoMap['Note']
            ->shouldReceive('fetchForOverview')
            ->with(111)
            ->once()
            ->andReturn('latest note');

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('fetchOpenApplicationsForLicence')
            ->with($query->getId())
            ->andReturn($openApplicationsForLicence);

        $sections = ['bar', 'cake'];

        $this->mockedSmServices['SectionAccessService']->shouldReceive('getAccessibleSectionsForLicence')
            ->once()
            ->with($licence)
            ->andReturn($sections);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'sections' => ['bar', 'cake'],
            'niFlag' => 'N',
            'isMlh' => true,
            'continuationMarker' => ['CD'],
            'latestNote' => 'latest note',
            'canHaveInspectionRequest' => true,
            'showExpiryWarning' => false,
            'isLicenceSurrenderAllowed' => $isLicenceSurrenderAllowed,
            'activeVehicleCount' => 1,
            'totalVehicleCount' => 1,
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dptestHandleQuery')]
    public function testHandleQueryNoContinuationDetail(mixed $isLicenceSurrenderAllowed, mixed $openApplicationsForLicence): void
    {
        $query = Qry::create(['id' => 111]);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);

        $licence->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getNiFlag')
            ->andReturn('N')
            ->once()
            ->getMock()
            ->shouldReceive('getOrganisation')->andReturn(
                m::mock(Organisation::class)->shouldReceive('isMlh')->once()
                    ->andReturn(true)
                    ->getMock()
            )
            ->shouldReceive('isSpecialRestricted')
            ->andReturn(true)
            ->once()
            ->shouldReceive('getStatus->getId')
            ->andReturn(LicenceEntity::LICENCE_STATUS_VALID);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchForLicence')->with(111)
            ->andReturn([]);
        $this->repoMap['Note']
            ->shouldReceive('fetchForOverview')
            ->with(111)
            ->once()
            ->andReturn('latest note');

        $this->repoMap['Licence']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('fetchOpenApplicationsForLicence')
            ->with($query->getId())
            ->andReturn($openApplicationsForLicence);

        $sections = ['bar', 'cake'];

        $this->mockedSmServices['SectionAccessService']->shouldReceive('getAccessibleSectionsForLicence')
            ->once()
            ->with($licence)
            ->andReturn($sections);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'sections' => ['bar', 'cake'],
            'niFlag' => 'N',
            'isMlh' => true,
            'continuationMarker' => null,
            'latestNote' => 'latest note',
            'canHaveInspectionRequest' => false,
            'showExpiryWarning' => false,
            'isLicenceSurrenderAllowed' => $isLicenceSurrenderAllowed,
            'activeVehicleCount' => 1,
            'totalVehicleCount' => 1,
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dptestHandleQueryShowExpiryWarningDataProvider')]
    public function testHandleQueryShowExpiryWarning(
        mixed $expected,
        mixed $isExpiring,
        mixed $isSystemParamDisabled,
        mixed $continuationDetailStatusId,
        mixed $isLicenceSurrenderAllowed,
        mixed $openApplicationsForLicence
    ): void {
        $query = Qry::create(['id' => 111]);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);

        $continuationDetail = new ContinuationDetail();
        $continuationDetail->setStatus(new RefData($continuationDetailStatusId));

        $licence->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getNiFlag')
            ->andReturn('N')
            ->once()
            ->getMock()
            ->shouldReceive('getOrganisation')->andReturn(
                m::mock(Organisation::class)->shouldReceive('isMlh')->once()
                    ->andReturn(true)
                    ->getMock()
            )
            ->shouldReceive('isSpecialRestricted')->andReturn(true)->once()
            ->shouldReceive('isExpiring')->with()->once()->andReturn($isExpiring)
            ->shouldReceive('getStatus->getId')
            ->andReturn(LicenceEntity::LICENCE_STATUS_VALID);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchForLicence')->with(111)
            ->andReturn([$continuationDetail]);
        $this->repoMap['Note']->shouldReceive('fetchForOverview')->with(111)->once()->andReturn('latest note');
        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($query)->andReturn($licence);
        $this->repoMap['SystemParameter']->shouldReceive('getDisabledDigitalContinuations')->with()
            ->andReturn($isSystemParamDisabled);

        $this->repoMap['Application']->shouldReceive('fetchOpenApplicationsForLicence')
            ->with($query->getId())
            ->andReturn($openApplicationsForLicence);

        $sections = ['bar', 'cake'];
        $this->mockedSmServices['SectionAccessService']->shouldReceive('getAccessibleSectionsForLicence')
            ->once()
            ->with($licence)
            ->andReturn($sections);

        $result = $this->sut->handleQuery($query);

        $resultArray = $result->serialize();

        $this->assertArrayHasKey('showExpiryWarning', $resultArray);
        $this->assertSame($expected, $resultArray['showExpiryWarning']);

        $this->assertArrayHasKey('isLicenceSurrenderAllowed', $resultArray);
        $this->assertSame($isLicenceSurrenderAllowed, $resultArray['isLicenceSurrenderAllowed']);
    }

    public static function dptestHandleQueryShowExpiryWarningDataProvider(): array
    {
        return [
            'should show' => [
                'expected' => true,
                'isExpiring' => true,
                'isSystemParamDisabled' => false,
                'continuationDetailStatusId' => ContinuationDetail::STATUS_PRINTED,
                'isLicenceSurrenderAllowed' => true,
                'openApplicationsForLicence' => [],
            ],
            'licence is expiring' => [
                'expected' => false,
                'isExpiring' => false,
                'isSystemParamDisabled' => false,
                'continuationDetailStatusId' => ContinuationDetail::STATUS_PRINTED,
                'isLicenceSurrenderAllowed' => true,
                'openApplicationsForLicence' => [],
            ],
            'system Parameter disabled' => [
                'expected' => false,
                'isExpiring' => true,
                'isSystemParamDisabled' => true,
                'continuationDetailStatusId' => ContinuationDetail::STATUS_PRINTED,
                'isLicenceSurrenderAllowed' => false,
                'openApplicationsForLicence' => ['some data'],
            ],
            'wrong continuation detail status' => [
                'expected' => false,
                'isExpiring' => true,
                'isSystemParamDisabled' => false,
                'continuationDetailStatusId' => ContinuationDetail::STATUS_PRINTING,
                'isLicenceSurrenderAllowed' => false,
                'openApplicationsForLicence' => ['some data'],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dptestHandleQuery')]
    public function testHandleQueryShowExpiryWarningNoContinuationDetail(
        mixed $isLicenceSurrenderAllowed,
        mixed $openApplicationsForLicence
    ): void {
        $query = Qry::create(['id' => 111]);

        /** @var LicenceEntity $licence */
        $licence = m::mock(LicenceEntity::class)->makePartial();
        $licence->setId(111);

        $licence->shouldReceive('serialize')
            ->andReturn(['foo' => 'bar'])
            ->shouldReceive('getNiFlag')
            ->andReturn('N')
            ->once()
            ->getMock()
            ->shouldReceive('getOrganisation')->andReturn(
                m::mock(Organisation::class)->shouldReceive('isMlh')->once()
                    ->andReturn(true)
                    ->getMock()
            )
            ->shouldReceive('isSpecialRestricted')->andReturn(true)->once()
            ->shouldReceive('getStatus->getId')
            ->andReturn(LicenceEntity::LICENCE_STATUS_VALID);

        $this->repoMap['ContinuationDetail']->shouldReceive('fetchForLicence')->with(111)
            ->andReturn([]);
        $this->repoMap['Note']->shouldReceive('fetchForOverview')->with(111)->once()->andReturn('latest note');
        $this->repoMap['Licence']->shouldReceive('fetchUsingId')->with($query)->andReturn($licence);

        $this->repoMap['Application']->shouldReceive('fetchOpenApplicationsForLicence')
            ->with($query->getId())
            ->andReturn($openApplicationsForLicence);

        $sections = ['bar', 'cake'];
        $this->mockedSmServices['SectionAccessService']->shouldReceive('getAccessibleSectionsForLicence')
            ->once()
            ->with($licence)
            ->andReturn($sections);

        $result = $this->sut->handleQuery($query);

        $expected = [
            'showExpiryWarning' => false,
            'isLicenceSurrenderAllowed' => $isLicenceSurrenderAllowed
        ];

        $serializedResult = $result->serialize();

        foreach ($expected as $key => $value) {
            $this->assertArrayHasKey($key, $serializedResult, "The key '{$key}' is missing in the result.");
            $this->assertSame($value, $serializedResult[$key], "The value of '{$key}' does not match the expected value.");
        }
    }

    public static function dptestHandleQuery(): array
    {
        return [
            [
                'isLicenceSurrenderAllowed' => true,
                'openApplicationsForLicence' => []
            ],
            [
                'isLicenceSurrenderAllowed' => false,
                'openApplicationsForLicence' => ['some data']
            ]
        ];
    }
}
