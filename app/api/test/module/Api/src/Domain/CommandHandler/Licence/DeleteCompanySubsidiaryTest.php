<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Licence;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Licence\DeleteCompanySubsidiary;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\User\Permission;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\Licence\DeleteCompanySubsidiary
 */
class DeleteCompanySubsidiaryTest extends AbstractCommandHandlerTestCase
{
    public const LICENCE_ID = 1111;
    public const TASK_ID = 877;
    public const VERSION = 99;

    /** @var  DeleteCompanySubsidiary|m\MockInterface */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockAuthSrv;

    public function setUp(): void
    {
        $this->sut = m::mock(DeleteCompanySubsidiary::class . '[delete, createTask]')
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
            'ids' => [111, 222, 333],
        ];
        $command = TransferCmd\Licence\DeleteCompanySubsidiary::create($data);

        //  mock is granted
        $this->mockIsGranted(Permission::SELFSERVE_USER, $isGranted);

        //  mock update result
        $this->sut
            ->shouldReceive('delete')
            ->once()
            ->with($command)
            ->andReturn(
                (new Result())
                    ->addMessage('Unit delete result')
            );

        //  mock create task
        if ($expectTask === true) {
            $mockEntity = m::mock(Repository\CompanySubsidiary::class)
                ->shouldReceive('getName')->times(3)->andReturn('Unit_EntityName')
                ->getMock();

            $this->repoMap['CompanySubsidiary']
                ->shouldReceive('fetchByIds')
                ->once()
                ->with([111, 222, 333])
                ->andReturn([$mockEntity, clone $mockEntity, clone $mockEntity]);

            $this->sut
                ->shouldReceive('createTask')
                ->times(3)
                ->with(self::LICENCE_ID, 'Subsidiary company removed - Unit_EntityName')
                ->andReturn(
                    (new Result())
                        ->addId('task', self::TASK_ID)
                        ->addMessage('Unit Task created')
                );
        } else {
            $this->repoMap['CompanySubsidiary']
                ->shouldReceive('fetchByIds')->never();
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
                    'Unit Task created',
                    'Unit Task created',
                    'Unit Task created',
                    'Unit delete result',
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
