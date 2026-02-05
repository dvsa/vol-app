<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Variation;

use ArrayObject;
use Dvsa\Olcs\Api\Domain\QueryHandlerManager;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Service\Publication\Context\Variation\Authorisations;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class AuthorisationsTest
 *
 * @author Jonathan Thomas <jonathan@opalise.co.uk>
 */
class AuthorisationsTest extends MockeryTestCase
{
    /**
     * @var Authorisations
     */
    private $sut;

    private $application;

    private $publicationLink;

    private $context;

    public function setUp(): void
    {
        $this->sut = new Authorisations(
            m::mock(QueryHandlerManager::class)
        );

        $this->application = m::mock(Application::class);

        $this->publicationLink = m::mock(PublicationLink::class);
        $this->publicationLink->shouldReceive('getApplication')
            ->withNoArgs()
            ->andReturn($this->application);

        $this->context = new ArrayObject();

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpSetContext')]
    public function testSetContext(
        mixed $hgvAuthorisationIncreased,
        mixed $vehicleTypeMixedWithLgv,
        mixed $lgvAuthorisationIncreased,
        mixed $lgvChangedFromNullToNumeric,
        mixed $totAuthLgvVehicles,
        mixed $trailerAuthorisationIncreased,
        mixed $expectedAuthorisationArray
    ): void {
        $totAuthHgvVehicles = 5;
        $totAuthTrailers = 3;

        $this->application->shouldReceive('hasHgvAuthorisationIncreased')
            ->withNoArgs()
            ->andReturn($hgvAuthorisationIncreased);
        $this->application->shouldReceive('isVehicleTypeMixedWithLgv')
            ->withNoArgs()
            ->andReturn($vehicleTypeMixedWithLgv);
        $this->application->shouldReceive('getTotAuthHgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthHgvVehicles);
        $this->application->shouldReceive('hasLgvAuthorisationIncreased')
            ->withNoArgs()
            ->andReturn($lgvAuthorisationIncreased);
        $this->application->shouldReceive('hasLgvAuthorisationChangedFromNullToNumeric')
            ->withNoArgs()
            ->andReturn($lgvChangedFromNullToNumeric);
        $this->application->shouldReceive('getTotAuthLgvVehicles')
            ->withNoArgs()
            ->andReturn($totAuthLgvVehicles);
        $this->application->shouldReceive('hasAuthTrailersIncrease')
            ->withNoArgs()
            ->andReturn($trailerAuthorisationIncreased);
        $this->application->shouldReceive('getTotAuthTrailers')
            ->withNoArgs()
            ->andReturn($totAuthTrailers);

        $this->assertSame(
            ['authorisation' => $expectedAuthorisationArray],
            $this->sut->provide($this->publicationLink, $this->context)->getArrayCopy()
        );
    }

    public static function dpSetContext(): array
    {
        return [
            'lgv auth increase only' => [
                'hgvAuthorisationIncreased' => false,
                'vehicleTypeMixedWithLgv' => false,
                'lgvAuthorisationIncreased' => true,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => 7,
                'trailerAuthorisationIncreased' => false,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 7 Light goods vehicle(s)'
                ]
            ],
            'lgv auth from null to zero' => [
                'hgvAuthorisationIncreased' => false,
                'vehicleTypeMixedWithLgv' => false,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => true,
                'totAuthLgvVehicles' => 0,
                'trailerAuthorisationIncreased' => false,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 0 Light goods vehicle(s)'
                ]
            ],
            'lgv auth from null to nonzero' => [
                'hgvAuthorisationIncreased' => false,
                'vehicleTypeMixedWithLgv' => false,
                'lgvAuthorisationIncreased' => true,
                'lgvChangedFromNullToNumeric' => true,
                'totAuthLgvVehicles' => 7,
                'trailerAuthorisationIncreased' => false,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 7 Light goods vehicle(s)'
                ]
            ],
            'hgv and lgv auth increase with hgv caption' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => true,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => 7,
                'trailerAuthorisationIncreased' => false,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 Heavy goods vehicle(s)',
                    'New licence authorisation will be 7 Light goods vehicle(s)'
                ]
            ],
            'hgv increase and lgv from null to zero with hgv caption' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => true,
                'totAuthLgvVehicles' => 0,
                'trailerAuthorisationIncreased' => false,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 Heavy goods vehicle(s)',
                    'New licence authorisation will be 0 Light goods vehicle(s)'
                ]
            ],
            'hgv increase and lgv from null to nonzero with hgv caption' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => true,
                'lgvChangedFromNullToNumeric' => true,
                'totAuthLgvVehicles' => 7,
                'trailerAuthorisationIncreased' => false,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 Heavy goods vehicle(s)',
                    'New licence authorisation will be 7 Light goods vehicle(s)'
                ]
            ],
            'all auths increased' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => true,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => 7,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 Heavy goods vehicle(s)',
                    'New licence authorisation will be 7 Light goods vehicle(s)',
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
            'hgv and trailer auth increase with vehicle caption' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => false,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => null,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 vehicle(s)',
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
            'hgv and trailer auth increase with hgv caption' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => null,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 Heavy goods vehicle(s)',
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
            'hgv and trailer auth increase and lgv auth from null to zero' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => true,
                'totAuthLgvVehicles' => 0,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 Heavy goods vehicle(s)',
                    'New licence authorisation will be 0 Light goods vehicle(s)',
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
            'hgv and trailer auth increase and lgv auth from null to nonzero' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => true,
                'lgvChangedFromNullToNumeric' => true,
                'totAuthLgvVehicles' => 7,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 Heavy goods vehicle(s)',
                    'New licence authorisation will be 7 Light goods vehicle(s)',
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
            'lgv auth increase and trailer auth increase' => [
                'hgvAuthorisationIncreased' => false,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => true,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => 7,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 7 Light goods vehicle(s)',
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
            'lgv auth from null to zero and trailer auth increase' => [
                'hgvAuthorisationIncreased' => false,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => true,
                'totAuthLgvVehicles' => 0,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 0 Light goods vehicle(s)',
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
            'lgv auth from null to nonzero and trailer auth increase' => [
                'hgvAuthorisationIncreased' => false,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => true,
                'lgvChangedFromNullToNumeric' => true,
                'totAuthLgvVehicles' => 7,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 7 Light goods vehicle(s)',
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
            'hgv auth increase only with vehicle caption' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => false,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => 0,
                'trailerAuthorisationIncreased' => false,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 vehicle(s)',
                ]
            ],
            'hgv auth increase only with hgv caption' => [
                'hgvAuthorisationIncreased' => true,
                'vehicleTypeMixedWithLgv' => true,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => 0,
                'trailerAuthorisationIncreased' => false,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 5 Heavy goods vehicle(s)',
                ]
            ],
            'trailer auth increase only' => [
                'hgvAuthorisationIncreased' => false,
                'vehicleTypeMixedWithLgv' => false,
                'lgvAuthorisationIncreased' => false,
                'lgvChangedFromNullToNumeric' => false,
                'totAuthLgvVehicles' => 0,
                'trailerAuthorisationIncreased' => true,
                "expectedAuthorisationArray" => [
                    'New licence authorisation will be 3 trailer(s)'
                ]
            ],
        ];
    }

    public function testDoNothingWhenAuthorisationNotIncreasedAndNotChangedFromNullToNumeric(): void
    {
        $this->application->shouldReceive('hasHgvAuthorisationIncreased')
            ->withNoArgs()
            ->andReturnFalse();
        $this->application->shouldReceive('hasLgvAuthorisationIncreased')
            ->withNoArgs()
            ->andReturnFalse();
        $this->application->shouldReceive('hasLgvAuthorisationChangedFromNullToNumeric')
            ->withNoArgs()
            ->andReturnFalse();
        $this->application->shouldReceive('hasAuthTrailersIncrease')
            ->withNoArgs()
            ->andReturnFalse();

        $this->assertCount(
            0,
            $this->sut->provide($this->publicationLink, $this->context)
        );
    }
}
