<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Service\Publication\Context\Variation;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\Publication\PublicationLink;
use Dvsa\Olcs\Api\Entity\Publication\PublicationSection;
use Dvsa\Olcs\Api\Service\Helper\FormatAddress;
use Mockery as m;
use Mockery\Adapter\Phpunit\MockeryTestCase;

/**
 * Class OperatingCentresTest
 *
 * @author Mat Evans <mat.evans@valtech.co.uk>
 */
class OperatingCentresTest extends MockeryTestCase
{
    /**
     * @var \Dvsa\Olcs\Api\Service\Publication\Context\Variation\OperatingCentres
     */
    private $sut;

    public function setUp(): void
    {
        $this->sut = new \Dvsa\Olcs\Api\Service\Publication\Context\Variation\OperatingCentres(
            m::mock(\Dvsa\Olcs\Api\Domain\QueryHandlerManager::class)
        );

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestProvideAdded')]
    public function testProvideAdded(mixed $isVehicleTypeMixedWithLgv, mixed $expectedContext): void
    {
        $publicationLink = $this->getPublicationLink($isVehicleTypeMixedWithLgv);
        $context = new \ArrayObject();

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $this->sut->setAddressFormatter($mockAddressFormatter);

        $operatingCentre43 = $this->getOperatingCentre(43);
        $operatingCentre82 = $this->getOperatingCentre(82);
        $operatingCentre653 = $this->getOperatingCentre(653);

        $this->addApplicationOperatingCentre($publicationLink, 'A', 12, 3, $operatingCentre43);
        $this->addApplicationOperatingCentre($publicationLink, 'A', 0, 2, $operatingCentre82);
        $this->addApplicationOperatingCentre($publicationLink, 'A', 4234, 0, $operatingCentre653);

        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre43->getAddress())->once()
            ->andReturn('ADDRESS43_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre82->getAddress())->once()
            ->andReturn('ADDRESS82_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre653->getAddress())->once()
            ->andReturn('ADDRESS653_FORMATTED');

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            $expectedContext,
            $context->getArrayCopy()
        );
    }

    public static function dpTestProvideAdded(): array
    {
        return [
            [
                false,
                [
                    'operatingCentres' => [
                        "New operating centre: ADDRESS43_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 12 vehicle(s), 3 trailer(s)",
                        "New operating centre: ADDRESS82_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 2 trailer(s)",
                        "New operating centre: ADDRESS653_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 4234 vehicle(s)",
                    ]
                ],
            ],
            [
                true,
                [
                    'operatingCentres' => [
                        "New operating centre: ADDRESS43_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 12 Heavy goods vehicle(s), 3 trailer(s)",
                        "New operating centre: ADDRESS82_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 2 trailer(s)",
                        "New operating centre: ADDRESS653_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 4234 Heavy goods vehicle(s)",
                    ]
                ],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestProvideS4Ignored')]
    public function testProvideS4Ignored(mixed $isVehicleTypeMixedWithLgv, mixed $expectedContext): void
    {
        $publicationLink = $this->getPublicationLink($isVehicleTypeMixedWithLgv);
        $context = new \ArrayObject();

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $this->sut->setAddressFormatter($mockAddressFormatter);

        $operatingCentre43 = $this->getOperatingCentre(43);
        $operatingCentre82 = $this->getOperatingCentre(82);
        $operatingCentre653 = $this->getOperatingCentre(653);

        $this->addApplicationOperatingCentre($publicationLink, 'A', 12, 3, $operatingCentre43);
        $this->addApplicationOperatingCentre($publicationLink, 'A', 0, 2, $operatingCentre82);
        $this->addApplicationOperatingCentre($publicationLink, 'A', 4234, 0, $operatingCentre653);

        $s4 = m::mock(\Dvsa\Olcs\Api\Entity\Application\S4::class);
        $publicationLink->getApplication()->getOperatingCentres()[1]->setS4($s4);

        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre43->getAddress())->once()
            ->andReturn('ADDRESS43_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre653->getAddress())->once()
            ->andReturn('ADDRESS653_FORMATTED');

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            $expectedContext,
            $context->getArrayCopy()
        );
    }

    public static function dpTestProvideS4Ignored(): array
    {
        return [
            [
                false,
                [
                    'operatingCentres' => [
                        "New operating centre: ADDRESS43_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 12 vehicle(s), 3 trailer(s)",
                        "New operating centre: ADDRESS653_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 4234 vehicle(s)",
                    ]
                ],
            ],
            [
                true,
                [
                    'operatingCentres' => [
                        "New operating centre: ADDRESS43_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 12 Heavy goods vehicle(s), 3 trailer(s)",
                        "New operating centre: ADDRESS653_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 4234 Heavy goods vehicle(s)",
                    ]
                ],
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestProvideUpdated')]
    public function testProvideUpdated(mixed $isVehicleTypeMixedWithLgv, mixed $expectedContext): void
    {
        $publicationLink = $this->getPublicationLink($isVehicleTypeMixedWithLgv);
        $context = new \ArrayObject();

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $this->sut->setAddressFormatter($mockAddressFormatter);

        $operatingCentre43 = $this->getOperatingCentre(43);
        $operatingCentre82 = $this->getOperatingCentre(82);
        $operatingCentre653 = $this->getOperatingCentre(653);

        $this->addApplicationOperatingCentre($publicationLink, 'U', 12, 3, $operatingCentre43);
        $this->addLicenceOperatingCentre($publicationLink, 11, 2, $operatingCentre43);

        $this->addApplicationOperatingCentre($publicationLink, 'U', 10, 2, $operatingCentre82);
        $this->addLicenceOperatingCentre($publicationLink, 10, 4, $operatingCentre82);

        $this->addApplicationOperatingCentre($publicationLink, 'U', 4234, 0, $operatingCentre653);
        $this->addLicenceOperatingCentre($publicationLink, 2, 0, $operatingCentre653);

        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre43->getAddress())->once()
            ->andReturn('ADDRESS43_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre82->getAddress())->once()
            ->andReturn('ADDRESS82_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre653->getAddress())->once()
            ->andReturn('ADDRESS653_FORMATTED');

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            $expectedContext,
            $context->getArrayCopy()
        );
    }

    public static function dpTestProvideUpdated(): array
    {
        return [
            [
                false,
                [
                    'operatingCentres' => [
                        "Increase at existing operating centre: ADDRESS43_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 12 vehicle(s), 3 trailer(s)",
                        "Decrease at existing operating centre: ADDRESS82_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 10 vehicle(s), 2 trailer(s)",
                        "Increase at existing operating centre: ADDRESS653_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 4234 vehicle(s)",
                    ]
                ],
            ],
            [
                true,
                [
                    'operatingCentres' => [
                        "Increase at existing operating centre: ADDRESS43_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 12 Heavy goods vehicle(s), 3 trailer(s)",
                        "Decrease at existing operating centre: ADDRESS82_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 10 Heavy goods vehicle(s), 2 trailer(s)",
                        "Increase at existing operating centre: ADDRESS653_FORMATTED\n" .
                            "New authorisation at this operating centre will be: 4234 Heavy goods vehicle(s)",
                    ]
                ],
            ],
        ];
    }

    public function testProvideUpdatedMissingLoc(): void
    {
        $publicationLink = $this->getPublicationLink(false);
        $context = new \ArrayObject();

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $this->sut->setAddressFormatter($mockAddressFormatter);

        $operatingCentre43 = $this->getOperatingCentre(43);

        $this->addApplicationOperatingCentre($publicationLink, 'U', 12, 3, $operatingCentre43);
        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            [
                'operatingCentres' => []
            ],
            $context->getArrayCopy()
        );
    }

    /**
     *
     * @param type $aocVehicles
     * @param type $aocTrailers
     * @param type $locVehicles
     * @param type $locTrailers
     * @param type $inc
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('dataProviderTestProvideUpdatedIncreaseDecrease')]
    public function testProvideUpdatedIncreaseDecrease(mixed $aocVehicles, mixed $aocTrailers, mixed $locVehicles, mixed $locTrailers, mixed $inc): void
    {
        $publicationLink = $this->getPublicationLink(false);
        $context = new \ArrayObject();

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $this->sut->setAddressFormatter($mockAddressFormatter);

        $operatingCentre43 = $this->getOperatingCentre(43);

        $this->addApplicationOperatingCentre($publicationLink, 'U', $aocVehicles, $aocTrailers, $operatingCentre43);
        $this->addLicenceOperatingCentre($publicationLink, $locVehicles, $locTrailers, $operatingCentre43);

        if ($inc !== null) {
            $mockAddressFormatter->shouldReceive('format')->with($operatingCentre43->getAddress())->once()
                ->andReturn('ADDRESS43_FORMATTED');
        }

        $this->sut->provide($publicationLink, $context);

        if ($inc === true) {
            $this->assertStringStartsWith('Increase', $context->getArrayCopy()['operatingCentres'][0]);
        } elseif ($inc === false) {
            $this->assertStringStartsWith('Decrease', $context->getArrayCopy()['operatingCentres'][0]);
        } else {
            $this->assertEmpty($context->getArrayCopy()['operatingCentres']);
        }
    }

    public static function dataProviderTestProvideUpdatedIncreaseDecrease(): array
    {
        return [
            [11, 20, 10, 20, true],
            [9, 20, 10, 20, false],
            [10, 21, 10, 20, true],
            [10, 19, 10, 20, false],
            [10, 20, 10, 20, null],
        ];
    }

    public function testProvideDeleted(): void
    {
        $publicationLink = $this->getPublicationLink(false);
        $context = new \ArrayObject();

        $mockAddressFormatter = m::mock(FormatAddress::class);
        $this->sut->setAddressFormatter($mockAddressFormatter);

        $operatingCentre43 = $this->getOperatingCentre(43);
        $operatingCentre82 = $this->getOperatingCentre(82);
        $operatingCentre653 = $this->getOperatingCentre(653);

        $this->addApplicationOperatingCentre($publicationLink, 'D', 12, 3, $operatingCentre43);
        $this->addApplicationOperatingCentre($publicationLink, 'D', 0, 2, $operatingCentre82);
        $this->addApplicationOperatingCentre($publicationLink, 'D', 4234, 0, $operatingCentre653);

        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre43->getAddress())->once()
            ->andReturn('ADDRESS43_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre82->getAddress())->once()
            ->andReturn('ADDRESS82_FORMATTED');
        $mockAddressFormatter->shouldReceive('format')->with($operatingCentre653->getAddress())->once()
            ->andReturn('ADDRESS653_FORMATTED');

        $this->sut->provide($publicationLink, $context);

        $this->assertSame(
            [
                'operatingCentres' => [
                    "Removed operating centre: ADDRESS43_FORMATTED",
                    "Removed operating centre: ADDRESS82_FORMATTED",
                    "Removed operating centre: ADDRESS653_FORMATTED",
                ]
            ],
            $context->getArrayCopy()
        );
    }

    /**
     * @param bool $isVehicleTypeMixedWithLgv
     *
     * @return PublicationLink
     */
    private function getPublicationLink(mixed $isVehicleTypeMixedWithLgv): mixed
    {
        $publicationLink = new PublicationLink();

        $publicationSection = new PublicationSection();
        $publicationSection->setId(PublicationSection::VAR_NEW_SECTION);
        $publicationLink->setPublicationSection($publicationSection);

        $organisation = new \Dvsa\Olcs\Api\Entity\Organisation\Organisation();
        $organisation->setName('ORG_NAME');

        $licence = m::mock(\Dvsa\Olcs\Api\Entity\Licence\Licence::class)->makePartial();
        $licence->setOperatingCentres(new ArrayCollection());
        $licence->setOrganisation($organisation);

        $publicationLink->setLicence($licence);

        $application = m::mock(\Dvsa\Olcs\Api\Entity\Application\Application::class)->makePartial();
        $application->initCollections();
        $application->setLicence($licence);
        $application->setIsVariation(true);
        $application->shouldReceive('isVehicleTypeMixedWithLgv')
            ->withNoArgs()
            ->andReturn($isVehicleTypeMixedWithLgv);

        $publicationLink->setApplication($application);

        return $publicationLink;
    }

    /**
     * @param string $action
     * @param int $vehiclesRequired
     * @param int $trailersRequired
     */
    private function addApplicationOperatingCentre(
        PublicationLink $publicationLink,
        mixed $action,
        mixed $vehiclesRequired,
        mixed $trailersRequired,
        \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre $operatingCentre
    ): void {
        $licence = $publicationLink->getLicence();
        $application = $publicationLink->getApplication();
        $application->setLicence($licence);

        $aoc = new ApplicationOperatingCentre($application, $operatingCentre);
        $aoc->setAction($action);
        $aoc->setNoOfVehiclesRequired($vehiclesRequired);
        $aoc->setNoOfTrailersRequired($trailersRequired);
        $aoc->setApplication($application);

        $application->addOperatingCentres($aoc);
    }

    /**
     *
     * @param type $vehiclesRequired
     * @param type $trailersRequired
     */
    private function addLicenceOperatingCentre(
        PublicationLink $publicationLink,
        mixed $vehiclesRequired,
        mixed $trailersRequired,
        \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre $operatingCentre
    ): void {
        $aoc = new LicenceOperatingCentre($publicationLink->getLicence(), $operatingCentre);
        $aoc->setNoOfVehiclesRequired($vehiclesRequired);
        $aoc->setNoOfTrailersRequired($trailersRequired);

        $publicationLink->getLicence()->addOperatingCentres($aoc);
    }

    /**
     *
     * @param int $operatingCentreId
     *
     * @return \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre
     */
    private function getOperatingCentre(mixed $operatingCentreId): mixed
    {
        $operatingCentre = new \Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre();
        $operatingCentre->setId($operatingCentreId);

        $address = new \Dvsa\Olcs\Api\Entity\ContactDetails\Address();
        $operatingCentre->setAddress($address);

        return $operatingCentre;
    }
}
