<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateFee;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateApplicationFee;
use Dvsa\Olcs\Api\Domain\Command\Application\CreateApplicationFee as Cmd;
use Dvsa\Olcs\Api\Domain\Command\Task\CreateTask;
use Dvsa\Olcs\Api\Domain\Repository\FeeType;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Api\Entity\User\Team;
use Dvsa\Olcs\Api\Entity\User\User;
use Mockery as m;
use Dvsa\Olcs\Api\Domain\Repository\Application;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Dvsa\Olcs\Api\Entity\Task\Task;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Application\Application as ApplicationEntity;
use Dvsa\Olcs\Api\Entity\Fee\FeeType as FeeTypeEntity;
use Dvsa\Olcs\Api\Entity\TrafficArea\TrafficArea;
use LmcRbacMvc\Service\AuthorizationService;
use Dvsa\Olcs\Api\Domain\Util\DateTime\DateTime;

/**
 * Create Application Fee Test
 *
 * @author Rob Caiger <rob@clocal.co.uk>
 */
class CreateApplicationFeeTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new CreateApplicationFee();
        $this->mockRepo('Application', Application::class);
        $this->mockRepo('FeeType', FeeType::class);

        $this->mockedSmServices = [
            AuthorizationService::class => m::mock(AuthorizationService::class)
        ];

        parent::setUp();
    }

    #[\Override]
    protected function initReferences(): void
    {
        $this->refData = [
            FeeTypeEntity::FEE_TYPE_APP,
            FeeTypeEntity::FEE_TYPE_GRANT,
            Licence::LICENCE_CATEGORY_GOODS_VEHICLE,
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
        ];

        $this->references = [
            TrafficArea::class => [
                TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE => m::mock(TrafficArea::class)
            ]
        ];

        parent::initReferences();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('feeTypeProvider')]
    public function testHandleCommand(mixed $feeTypeFeeType, mixed $description, mixed $expectedDate): void
    {
        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('isGranted')
            ->once()
            ->with(Permission::INTERNAL_USER, null)
            ->andReturn(true);

        /** @var Team $mockTeam */
        $mockTeam = m::mock(Team::class)->makePartial();
        $mockTeam->setId(2);

        /** @var User $mockUser */
        $mockUser = m::mock(User::class)->makePartial();
        $mockUser->setId(1);
        $mockUser->setTeam($mockTeam);

        $this->mockedSmServices[AuthorizationService::class]->shouldReceive('getIdentity->getUser')
            ->andReturn($mockUser);

        $command = Cmd::create(
            [
                'id' => 111,
                'feeTypeFeeType' => $feeTypeFeeType,
                'description' => $description,
            ]
        );

        /** @var Licence $licence */
        $licence = m::mock(Licence::class)->makePartial();
        $licence->setId(222);

        /** @var ApplicationEntity $application */
        $application = m::mock(ApplicationEntity::class)->makePartial();
        $application->setId(111);
        $application->setLicence($licence);
        $application->setNiFlag('Y');
        $application->setGoodsOrPsv($this->refData[Licence::LICENCE_CATEGORY_GOODS_VEHICLE]);
        $application->setLicenceType($this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL]);
        $application->setReceivedDate('2015-01-01');

        /** @var FeeTypeEntity $feeType */
        $feeType = m::mock(FeeTypeEntity::class)->makePartial();
        $feeType->setId(444);
        $feeType->setDescription('DESC');
        $feeType->setFixedValue(10.5);

        $this->repoMap['FeeType']->shouldReceive('fetchLatest')
            ->with(
                $this->refData[$feeTypeFeeType],
                $this->refData[Licence::LICENCE_CATEGORY_GOODS_VEHICLE],
                $this->refData[Licence::LICENCE_TYPE_STANDARD_NATIONAL],
                m::type('\DateTime'),
                $this->references[TrafficArea::class][TrafficArea::NORTHERN_IRELAND_TRAFFIC_AREA_CODE]
            )
            ->andReturn($feeType);

        $this->repoMap['Application']
            ->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($application);

        $result1 = new Result();
        $result1->addId('task', 333);
        $taskData = [
            'category' => Task::CATEGORY_APPLICATION,
            'subCategory' => Task::SUBCATEGORY_FEE_DUE,
            'description' => $description,
            'actionDate' => $expectedDate,
            'assignedToUser' => 1,
            'assignedToTeam' => 2,
            'application' => 111,
            'licence' => 222,
            'isClosed' => false,
            'urgent' => false,
            'busReg' => null,
            'case' => null,
            'transportManager' => null,
            'irfoOrganisation' => null,
        ];
        $this->expectedSideEffect(CreateTask::class, $taskData, $result1);

        $result2 = new Result();
        $result2->addId('fee', 555);
        $feeData = [
            'id' => 111,
            'feeTypeFeeType' => $feeTypeFeeType,
            'task' => 333
        ];
        $this->expectedSideEffect(CreateFee::class, $feeData, $result2);

        $result = $this->sut->handleCommand($command);

        $expected = [
            'id' => [
                'task' => 333,
                'fee' => 555
            ],
            'messages' => []
        ];

        $this->assertEquals($expected, $result->toArray());
    }

    public static function feeTypeProvider(): array
    {
        return [
            [
                FeeTypeEntity::FEE_TYPE_APP,
                'Application Fee Due',
                (new DateTime('now'))->format(CreateApplicationFee::DUE_DATE_FORMAT)
            ],
            [
                FeeTypeEntity::FEE_TYPE_GRANT,
                'Grant fee due',
                ((new DateTime('now'))->add(new \DateInterval('P14D'))->format(CreateApplicationFee::DUE_DATE_FORMAT))
            ]
        ];
    }
}
