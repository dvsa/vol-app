<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateBusinessDetails;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\Licence\UpdateBusinessDetails
 */
class UpdateBusinessDetailsTest extends AbstractCommandHandlerTestCase
{
    public const ID = 111;

    /** @var  UpdateBusinessDetails */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockAuthSrv;

    public function setUp(): void
    {
        $this->sut = new UpdateBusinessDetails();

        $this->mockAuthSrv = m::mock(AuthorizationService::class);
        $this->mockedSmServices[AuthorizationService::class] = $this->mockAuthSrv;

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        //  save business details
        $saveCmdData = [
            'id' => self::ID,
            'version' => 1,
            'name' => null,
            'natureOfBusiness' => null,
            'companyOrLlpNo' => null,
            'registeredAddress' => null,
            'tradingNames' => [],
            'partial' => null,
            'allowEmail' => null,
        ];

        $saveCmdResult = new Result();
        $saveCmdResult->addMessage('Business Details updated');
        $saveCmdResult->setFlag('tradingNamesChanged', true);

        $this->expectedSideEffect(DomainCmd\Licence\SaveBusinessDetails::class, $saveCmdData, $saveCmdResult);

        //  mock permissions
        $this->mockIsGranted(Permission::SELFSERVE_USER, true);

        //  create task
        $taskCmdData = [
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_BUSINESS_DETAILS_CHANGE,
            'description' => 'Change to business details',
            'licence' => self::ID,
        ];

        $taskCmdResult = new Result();
        $taskCmdResult->addMessage('Task Created');

        $this->expectedSideEffect(DomainCmd\Task\CreateTask::class, $taskCmdData, $taskCmdResult);

        //  call
        $data = [
            'id' => self::ID,
            'version' => 1,
        ];
        $command = TransferCmd\Licence\UpdateBusinessDetails::create($data);

        $actual = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Business Details updated',
                'Task Created',
            ],
            'flags' => ['tradingNamesChanged' => 1]
        ];

        static::assertEquals($expected, $actual->toArray());
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCmdTaskNotCreated')]
    public function testHandleCmdTaskNotCreated(mixed $hasChanged, mixed $isGranted): void
    {
        $saveCmdResult = new Result();
        $saveCmdResult->addMessage('Business Details updated');
        $saveCmdResult->setFlag('tradingNamesChanged', $hasChanged);

        $this->expectedSideEffect(DomainCmd\Licence\SaveBusinessDetails::class, [], $saveCmdResult);

        //  mock permissions
        $this->mockIsGranted(Permission::SELFSERVE_USER, $isGranted);

        //  call
        $actual = $this->sut->handleCommand(
            TransferCmd\Licence\UpdateBusinessDetails::create(['id' => self::ID])
        );

        $expected = [
            'id' => [],
            'messages' => [
                'Business Details updated',
            ],
            'flags' => ['tradingNamesChanged' => $hasChanged]
        ];

        static::assertEquals($expected, $actual->toArray());
    }

    public static function dpTestHandleCmdTaskNotCreated(): array
    {
        return [
            [
                'hasChanged' => true,
                'isGranted' => false,
            ],
            [
                'hasChanged' => false,
                'isGranted' => true,
            ],
            [
                'hasChanged' => false,
                'isGranted' => false,
            ],
        ];
    }

    private function mockIsGranted(mixed $permission, mixed $result): void
    {
        $this->mockAuthSrv
            ->shouldReceive('isGranted')
            ->with($permission, null)
            ->andReturn($result);
    }
}
