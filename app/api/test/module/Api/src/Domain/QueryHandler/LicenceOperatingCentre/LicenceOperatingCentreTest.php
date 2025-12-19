<?php

/**
 * Licence Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */

namespace Dvsa\OlcsTest\Api\Domain\QueryHandler\LicenceOperatingCentre;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\Role;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\QueryHandler\LicenceOperatingCentre\LicenceOperatingCentre;
use Dvsa\OlcsTest\Api\Domain\QueryHandler\QueryHandlerTestCase;
use Dvsa\Olcs\Transfer\Query\LicenceOperatingCentre\LicenceOperatingCentre as Qry;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre as LicenceOperatingCentreEntity;
use LmcRbacMvc\Identity\IdentityInterface;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * Licence Operating Centre Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class LicenceOperatingCentreTest extends QueryHandlerTestCase
{
    protected $expectedBundle = [
        'operatingCentre' => [
            'address' => [
                'countryCode'
            ],
            'adDocuments' => [
                'application'
            ]
        ]
    ];

    public function setUp(): void
    {
        $this->sut = new LicenceOperatingCentre();
        $this->mockRepo('LicenceOperatingCentre', \Dvsa\Olcs\Api\Domain\Repository\LicenceOperatingCentre::class);

        $this->mockedSmServices[AuthorizationService::class] = m::mock(AuthorizationService::class);

        parent::setUp();
    }

    public function testHandleQuery()
    {
        $query = Qry::create(['id' => 111]);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('isPsv')->with()->once()->andReturn(true);
        $licence->shouldReceive('getNiFlag')->with()->once()->andReturn('Y');

        /** @var LicenceOperatingCentreEntity $loc */
        $loc = m::mock(LicenceOperatingCentreEntity::class)->makePartial();
        $loc->setLicence($licence);

        $loc->shouldReceive('serialize')
            ->with($this->expectedBundle)
            ->andReturn(['foo' => 'bar']);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($loc);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $this->setAuthUser();
        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'isPsv' => true,
            'canUpdateAddress' => true,
            'wouldIncreaseRequireAdditionalAdvertisement' => false,
            'currentVehiclesRequired' => null,
            'currentTrailersRequired' => null,
            'niFlag' => 'Y',
        ];

        $this->assertEquals($expected, $result->serialize());
    }

    public function testHandleQueryReadOnly()
    {
        $this->expectedBundle = [
            'operatingCentre' => [
                'address' => [
                    'countryCode'
                ]
            ]
        ];
        $query = Qry::create(['id' => 111]);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->shouldReceive('isPsv')->with()->once()->andReturn(true);
        $licence->shouldReceive('getNiFlag')->with()->once()->andReturn('Y');

        /** @var LicenceOperatingCentreEntity $loc */
        $loc = m::mock(LicenceOperatingCentreEntity::class)->makePartial();
        $loc->setLicence($licence);

        $loc->shouldReceive('serialize')
            ->with($this->expectedBundle)
            ->andReturn(['foo' => 'bar']);

        $this->repoMap['LicenceOperatingCentre']->shouldReceive('fetchUsingId')
            ->with($query)
            ->andReturn($loc);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        $mockedRole = m::mock(Role::class);
        $mockedRole->shouldReceive('getRole')->andReturn(Role::ROLE_INTERNAL_LIMITED_READ_ONLY);

        $this->setAuthUser($mockedRole);
        $result = $this->sut->handleQuery($query);

        $expected = [
            'foo' => 'bar',
            'isPsv' => true,
            'canUpdateAddress' => true,
            'wouldIncreaseRequireAdditionalAdvertisement' => false,
            'currentVehiclesRequired' => null,
            'currentTrailersRequired' => null,
            'niFlag' => 'Y',
        ];

        $this->assertEquals($expected, $result->serialize());
    }
    protected function setAuthUser($role = null)
    {
        if (is_null($role)) {
            $role = new ArrayCollection([]);
        } else {
            $role = new ArrayCollection([$role]);
        }
        $mockedId = m::mock(IdentityInterface::class)->shouldReceive('getUser')->andReturn(
            m::mock(User::class)->shouldReceive('getRoles')->andReturn($role)->getMock()
        )->getMock();

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity')->once()->andReturn(
            $mockedId
        );
    }
}
