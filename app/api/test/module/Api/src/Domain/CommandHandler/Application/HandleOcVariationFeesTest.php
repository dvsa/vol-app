<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\Common\Collections\ArrayCollection;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee;
use Dvsa\Olcs\Api\Domain\Command\Fee\CancelFee;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\Application;
use Dvsa\Olcs\Api\Entity\Application\ApplicationOperatingCentre;
use Dvsa\Olcs\Api\Entity\Fee\Fee;
use Dvsa\Olcs\Api\Entity\Fee\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Licence\LicenceOperatingCentre;
use Dvsa\Olcs\Api\Entity\OperatingCentre\OperatingCentre;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\HandleOcVariationFees as CommandHandler;
use Dvsa\Olcs\Api\Domain\Command\Application\HandleOcVariationFees as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Domain\Repository;

/**
 * Handle Oc Variation Fees Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
#[\PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations]
class HandleOcVariationFeesTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CommandHandler();
        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('Fee', Repository\Fee::class);

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            Application::APPLIED_VIA_POST,
        ];

        parent::initReferences();
    }

    public function testHandleCommandWithAddedOcWithoutFeesGoods(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('A');
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial()
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->once()
            ->getMock();

        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $application
            ->shouldReceive('hasApplicationFee')
            ->andReturn(false);

        $data = [
            'id' => 111,
            'feeTypeFeeType' => FeeType::FEE_TYPE_VAR,
            'description' => null
        ];
        $result = new Result();
        $result->addMessage('CreateApplicationFee');
        $this->expectedSideEffect(CreateApplicationFee::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreateApplicationFee'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithAddedOcPsv(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('A');
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial()
            ->shouldReceive('isGoods')
            ->andReturn(false)
            ->once()
            ->getMock();

        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingFeesByApplicationId')
            ->with(111)
            ->once()
            ->andReturn([])
            ->getMock();

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithAddedOcWithFeesGoods(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('A');
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial()
            ->shouldReceive('isGoods')
            ->andReturn(true)
            ->once()
            ->getMock();

        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $application
            ->shouldReceive('hasApplicationFee')
            ->andReturn(true);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpHandleCommandForGoodsWithIncreasedAuthWithoutFees(): array
    {
        return [
            'HGV auth increased' => [
                'hasHgvAuthorisationIncreased' => true,
                'hasLgvAuthorisationIncreased' => false,
                'hasAuthTrailersIncrease' => false,
            ],
            'LGV auth increased' => [
                'hasHgvAuthorisationIncreased' => false,
                'hasLgvAuthorisationIncreased' => true,
                'hasAuthTrailersIncrease' => false,
            ],
            'Trailers auth increased' => [
                'hasHgvAuthorisationIncreased' => false,
                'hasLgvAuthorisationIncreased' => false,
                'hasAuthTrailersIncrease' => true,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandForGoodsWithIncreasedAuthWithoutFees')]
    public function testHandleCommandForGoodsWithIncreasedAuthWithoutFees(mixed $hasHgvAuthorisationIncreased, mixed $hasLgvAuthorisationIncreased, mixed $hasAuthTrailersIncrease): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setLicence($licence);
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $application
            ->shouldReceive('isGoods')
            ->withNoArgs()
            ->andReturnTrue()
            ->shouldReceive('hasHgvAuthorisationIncreased')
            ->withNoArgs()
            ->andReturn($hasHgvAuthorisationIncreased)
            ->shouldReceive('hasLgvAuthorisationIncreased')
            ->withNoArgs()
            ->andReturn($hasLgvAuthorisationIncreased)
            ->shouldReceive('hasAuthTrailersIncrease')
            ->withNoArgs()
            ->andReturn($hasAuthTrailersIncrease)
            ->shouldReceive('hasApplicationFee')
            ->andReturn(false);

        $this->expectedSideEffect(
            CreateApplicationFee::class,
            [
                'id' => 111,
                'feeTypeFeeType' => FeeType::FEE_TYPE_VAR,
                'description' => null
            ],
            (new Result())->addMessage('CreateApplicationFee')
        );

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreateApplicationFee'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function dpHandleCommandForPsvWithUpdatedAuthWithoutFees(): array
    {
        return [
            'HGV auth increased' => [
                'hasHgvAuthorisationIncreased' => true,
            ],
            'HGV auth not increased' => [
                'hasHgvAuthorisationIncreased' => false,
            ],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpHandleCommandForPsvWithUpdatedAuthWithoutFees')]
    public function testHandleCommandForPsvWithUpdatedAuthWithoutFees(mixed $hasHgvAuthorisationIncreased): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setLicence($licence);
        $application->setId(111);
        $application->setOperatingCentres(new ArrayCollection());

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $application
            ->shouldReceive('isGoods')
            ->withNoArgs()
            ->andReturnFalse()
            ->shouldReceive('hasHgvAuthorisationIncreased')
            ->withNoArgs()
            ->andReturn($hasHgvAuthorisationIncreased)
            ->shouldReceive('hasLgvAuthorisationIncreased')
            ->withNoArgs()
            ->andReturnFalse()
            ->shouldReceive('hasAuthTrailersIncrease')
            ->withNoArgs()
            ->andReturnFalse()
            ->shouldReceive('hasApplicationFee')
            ->withNoArgs()
            ->andReturnFalse();

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingFeesByApplicationId')
            ->with(111)
            ->once()
            ->andReturn([]);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithUpdatedOcWithIncreasedVehiclesWithoutFees(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setOperatingCentre($oc);
        $loc->setNoOfVehiclesRequired(10);
        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('U');
        $aoc->setOperatingCentre($oc);
        $aoc->setNoOfVehiclesRequired(15);
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $application
            ->shouldReceive('hasApplicationFee')
            ->andReturn(false);

        $data = [
            'id' => 111,
            'feeTypeFeeType' => FeeType::FEE_TYPE_VAR,
            'description' => null
        ];
        $result = new Result();
        $result->addMessage('CreateApplicationFee');
        $this->expectedSideEffect(CreateApplicationFee::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreateApplicationFee'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithUpdatedOcWithIncreasedTrailersWithoutFees(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc->setOperatingCentre($oc);
        $loc->setNoOfVehiclesRequired(10);
        $loc->setNoOfTrailersRequired(10);
        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('U');
        $aoc->setOperatingCentre($oc);
        $aoc->setNoOfVehiclesRequired(10);
        $aoc->setNoOfTrailersRequired(20);
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $application
            ->shouldReceive('hasApplicationFee')
            ->andReturn(false);

        $data = [
            'id' => 111,
            'feeTypeFeeType' => FeeType::FEE_TYPE_VAR,
            'description' => null
        ];
        $result = new Result();
        $result->addMessage('CreateApplicationFee');
        $this->expectedSideEffect(CreateApplicationFee::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'CreateApplicationFee'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithUpdatedOcWithoutIncreaseWithoutFees(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();
        /** @var OperatingCentre $oc2 */
        $oc2 = m::mock(OperatingCentre::class)->makePartial();

        /** @var LicenceOperatingCentre $loc1 */
        $loc1 = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc1->setOperatingCentre($oc2);
        /** @var LicenceOperatingCentre $loc2 */
        $loc2 = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc2->setOperatingCentre($oc);
        $loc2->setNoOfVehiclesRequired(10);
        $loc2->setNoOfTrailersRequired(10);
        $locs = new ArrayCollection();
        $locs->add($loc1);
        $locs->add($loc2);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('U');
        $aoc->setOperatingCentre($oc);
        $aoc->setNoOfVehiclesRequired(10);
        $aoc->setNoOfTrailersRequired(10);
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $application
            ->shouldReceive('hasApplicationFee')
            ->andReturn(false);

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingFeesByApplicationId')
            ->with(111)
            ->once()
            ->andReturn([]);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithUpdatedOcWithoutIncreaseWithFees(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var OperatingCentre $oc */
        $oc = m::mock(OperatingCentre::class)->makePartial();
        /** @var OperatingCentre $oc2 */
        $oc2 = m::mock(OperatingCentre::class)->makePartial();

        /** @var LicenceOperatingCentre $loc1 */
        $loc1 = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc1->setOperatingCentre($oc2);
        /** @var LicenceOperatingCentre $loc2 */
        $loc2 = m::mock(LicenceOperatingCentre::class)->makePartial();
        $loc2->setOperatingCentre($oc);
        $loc2->setNoOfVehiclesRequired(10);
        $loc2->setNoOfTrailersRequired(10);
        $locs = new ArrayCollection();
        $locs->add($loc1);
        $locs->add($loc2);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('U');
        $aoc->setOperatingCentre($oc);
        $aoc->setNoOfVehiclesRequired(10);
        $aoc->setNoOfTrailersRequired(10);
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId(111);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        /** @var Fee $fee */
        $fee = m::mock(Fee::class)->makePartial();
        $fee->setId(123);

        $fees = [
            $fee
        ];

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingFeesByApplicationId')
            ->with(111)
            ->once()
            ->andReturn($fees);

        $data = [
            'id' => 123
        ];
        $result = new Result();
        $result->addMessage('CancelFee');
        $this->expectedSideEffect(CancelFee::class, $data, $result);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                '1 Fee(s) cancelled'
            ]
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandWithAddedOcWithoutFeesInternal(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('A');
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial();
        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId(111);
        $application->setAppliedVia($this->mapRefData(Application::APPLIED_VIA_POST));

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [],
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public function testHandleCommandPSVWithIncreasedAuthShouldCreateFee(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('A');
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);
        $licence->setTotAuthVehicles(1);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial()
            ->shouldReceive('isPsv')
            ->andReturn(true)
            ->once()
            ->shouldReceive('isVariation')
            ->andReturn(true)
            ->once()
            ->shouldReceive('hasApplicationFee')
            ->andReturn(false)
            ->getMock();

        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId($applicationId = 111);
        $application->setTotAuthVehicles(2);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $data = [
            'id' => $applicationId,
            'feeTypeFeeType' => FeeType::FEE_TYPE_VAR,
            'description' => null
        ];
        $result = new Result();
        $this->expectedSideEffect(CreateApplicationFee::class, $data, $result);

        $result = $this->sut->handleCommand($command);
    }

    public function testHandleCommandPSVWithoutIncreasedAuthShouldNotCreateFee(): void
    {
        $data = [];
        $command = Cmd::create($data);

        /** @var LicenceOperatingCentre $loc */
        $loc = m::mock(LicenceOperatingCentre::class)->makePartial();
        $locs = new ArrayCollection();
        $locs->add($loc);

        /** @var ApplicationOperatingCentre $aoc */
        $aoc = m::mock(ApplicationOperatingCentre::class)->makePartial();
        $aoc->setAction('A');
        $aocs = new ArrayCollection();
        $aocs->add($aoc);

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setOperatingCentres($locs);
        $licence->setTotAuthVehicles(1);

        /** @var Application $application */
        $application = m::mock(Application::class)->makePartial()
            ->shouldReceive('isPsv')
            ->andReturn(true)
            ->once()
            ->shouldReceive('isVariation')
            ->andReturn(true)
            ->once()
            ->shouldReceive('hasApplicationFee')
            ->andReturn(false)
            ->getMock();

        $application->setOperatingCentres($aocs);
        $application->setLicence($licence);
        $application->setId($applicationId = 111);
        $application->setTotAuthVehicles(1);

        $this->repoMap['Application']->shouldReceive('fetchUsingId')
            ->with($command)
            ->andReturn($application);

        $this->repoMap['Fee']->shouldReceive('fetchOutstandingFeesByApplicationId')
            ->with($applicationId)
            ->andReturn([]);

        $this->sut->handleCommand($command);
    }
}
