<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreateCompanySubsidiary;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\Licence\CreateCompanySubsidiary
 */
class CreateCompanySubsidiaryTest extends AbstractCommandHandlerTestCase
{
    public const LICENCE_ID = 1111;
    public const TASK_ID = 877;

    /** @var  CreateCompanySubsidiary|m\MockInterface */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockAuthSrv;

    public function setUp(): void
    {
        $this->sut = m::mock(CreateCompanySubsidiary::class . '[create, createTask]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('CompanySubsidiary', Repository\CompanySubsidiary::class);

        //  mock services
        $this->mockAuthSrv = m::mock(AuthorizationService::class);
        $this->mockedSmServices[AuthorizationService::class] = $this->mockAuthSrv;

        parent::setUp();
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('dpTestHandleCommand')]
    public function testHandleCommand(mixed $isGranted, mixed $expectTask): void
    {
        $data = [
            'licence' => self::LICENCE_ID,
            'name' => 'unit_Name',
        ];
        $command = TransferCmd\Licence\CreateCompanySubsidiary::create($data);

        //  mock is granted
        $this->mockIsGranted(Permission::SELFSERVE_USER, $isGranted);

        //  mock create result
        $this->sut->shouldReceive('create')
            ->once()
            ->with($command, self::LICENCE_ID)
            ->andReturn(
                (new Result())
                    ->addMessage('Unit Company Subsidiary Added')
            );

        //  mock create task
        if ($expectTask === true) {
            $this->sut->shouldReceive('createTask')
                ->once()
                ->with(self::LICENCE_ID, 'Subsidiary company added - unit_Name')
                ->andReturn(
                    (new Result())
                        ->addId('task', self::TASK_ID)
                        ->addMessage('Task created')
                );
        } else {
            $this->sut->shouldReceive('createTask')->never();
        }

        //  call
        $actual = $this->sut->handleCommand($command);

        static::assertInstanceOf(Result::class, $actual);

        if ($expectTask === true) {
            $expected = [
                'id' => [
                    'task' => self::TASK_ID,
                ],
                'messages' => [
                    'Unit Company Subsidiary Added',
                    'Task created',
                ],
            ];
            static::assertEquals($expected, $actual->toArray());
        }
    }

    public static function dpTestHandleCommand(): array
    {
        return [
            [
                'isGranted' => true,
                'expectTask' => true,
            ],
            [
                'isGranted' => false,
                'expectTask' => false,
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
