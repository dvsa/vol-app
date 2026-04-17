<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Lva;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\CommandHandler\Lva\AbstractCompanySubsidiary;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;
use LmcRbacMvc\Service\AuthorizationService;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\Lva\AbstractCompanySubsidiary
 */
class AbstractCompanySubsidiaryTest extends AbstractCommandHandlerTestCase
{
    public const ID = 1111;
    public const LICENCE_ID = 2222;
    public const TASK_ID = 7777;
    public const APP_ID = 8888;
    public const VERSION = 99;

    /** @var  AbstractCompanySubsidiaryStub */
    protected $sut;
    /** @var  m\MockInterface */
    private $mockLicenceEntity;

    public function setUp(): void
    {
        $this->sut = new class extends AbstractCompanySubsidiary {
            public function create($command, $licenceId)
            {
                return parent::create($command, $licenceId);
            }

            public function update($command)
            {
                return parent::update($command);
            }

            public function delete($command)
            {
                return parent::delete($command);
            }

            public function createTask($licenceId, $desc)
            {
                return parent::createTask($licenceId, $desc);
            }

            public function updateApplicationCompetition($appId, $hasChanged = true)
            {
                return parent::updateApplicationCompetition($appId, $hasChanged);
            }

            public function handleCommand(CommandInterface $command): void
            {
            }
        };

        //  mock entities
        $this->mockLicenceEntity = m::mock(Entity\Licence\Licence::class);

        //  mock repositories
        $this->mockRepo('Licence', Repository\Licence::class);
        $this->mockRepo('CompanySubsidiary', Repository\CompanySubsidiary::class);

        parent::setUp();
    }

    public function testCreate(): void
    {
        $data = [
            'name' => 'unit_OrgName',
            'companyNo' => 'unit_123456798',
        ];
        $command = TransferCmd\Application\CreateCompanySubsidiary::create($data);

        //  mock db
        $this->repoMap['Licence']
            ->shouldReceive('fetchById')
            ->once()
            ->with(self::LICENCE_ID)
            ->andReturn($this->mockLicenceEntity);

        $this->repoMap['CompanySubsidiary']
            ->shouldReceive('save')
            ->once()
            ->andReturnUsing(
                function (Entity\Organisation\CompanySubsidiary $entity) {
                    static::assertSame($this->mockLicenceEntity, $entity->getLicence());
                    static::assertEquals('unit_OrgName', $entity->getName());
                    static::assertEquals('unit_123456798', $entity->getCompanyNo());

                    $entity->setId(self::ID);
                }
            );

        //  call
        $actual = $this->sut->create($command, self::LICENCE_ID);

        static::assertInstanceOf(DomainCmd\Result::class, $actual);
        static::assertEquals(
            [
                'id' => [
                    'companySubsidiary' => self::ID,
                ],
                'messages' => [
                    'Company Subsidiary created',
                ],
            ],
            $actual->toArray()
        );
    }

    public function testUpdate(): void
    {
        $data = [
            'name' => 'unit_OrgName',
            'companyNo' => 'unit_123456798',
            'version' => self::VERSION,
        ];
        $command = TransferCmd\Application\UpdateCompanySubsidiary::create($data);

        //  mock db
        $mockSubsidiaryEntity = m::mock(Entity\Organisation\CompanySubsidiary::class)
            ->shouldReceive('setName')->once()->with('unit_OrgName')->andReturnSelf()
            ->shouldReceive('setCompanyNo')->once()->with('unit_123456798')->andReturnSelf()
            ->shouldReceive('getVersion')->once()->with()->andReturn(self::VERSION + 1)
            ->getMock();

        $this->repoMap['CompanySubsidiary']
            ->shouldReceive('fetchUsingId')
            ->once()
            ->with($command, Query::HYDRATE_OBJECT, self::VERSION)
            ->andReturn($mockSubsidiaryEntity)
            //
            ->shouldReceive('save')->once()->with($mockSubsidiaryEntity);

        //  call
        $actual = $this->sut->update($command);

        static::assertInstanceOf(DomainCmd\Result::class, $actual);
        static::assertEquals(
            [
                'id' => [],
                'messages' => [
                    'Company Subsidiary updated',
                ],
                'flags' => ['hasChanged' => 1]
            ],
            $actual->toArray()
        );
        static::assertTrue($actual->getFlag('hasChanged'));
    }

    public function testDelete(): void
    {
        $expectIds = [111, 222];
        $data = [
            'ids' => $expectIds,
        ];
        $command = TransferCmd\Application\DeleteCompanySubsidiary::create($data);

        //  mock db
        $mockCsEntity = m::mock(Entity\Organisation\CompanySubsidiary::class);
        $mockCsEntity2 = clone $mockCsEntity;

        $this->repoMap['CompanySubsidiary']
            ->shouldReceive('fetchByIds')
            ->once()
            ->with($expectIds)
            ->andReturn([$mockCsEntity, $mockCsEntity2])
            //
            ->shouldReceive('delete')->twice()->with(m::anyOf($mockCsEntity, $mockCsEntity2));

        //  call
        $actual = $this->sut->delete($command);

        static::assertInstanceOf(DomainCmd\Result::class, $actual);
        static::assertEquals(
            [
                'id' => [],
                'messages' => [
                    '2 Company Subsidiaries removed',
                ],
            ],
            $actual->toArray()
        );
    }

    public function testCreateTask(): void
    {
        $expectDesc = 'unit_Desc';

        $expectedData = [
            'category' => Category::CATEGORY_APPLICATION,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SUBSIDIARY_DIGITAL,
            'description' => $expectDesc,
            'licence' => self::LICENCE_ID,
        ];

        $resultTask = (new DomainCmd\Result())
            ->addId('task', self::TASK_ID)
            ->addMessage('Unit Task Created');
        $this->expectedSideEffect(DomainCmd\Task\CreateTask::class, $expectedData, $resultTask);

        //  call
        $actual = $this->sut->createTask(self::LICENCE_ID, $expectDesc);

        static::assertInstanceOf(DomainCmd\Result::class, $actual);
        static::assertEquals(
            [
                'id' => [
                    'task' => self::TASK_ID,
                ],
                'messages' => [
                    'Unit Task Created',
                ],
            ],
            $actual->toArray()
        );
    }

    public function testUpdateAppCompetition(): void
    {
        $expectHasChanged = 'unit_HasChanged';

        $this->expectedSideEffect(
            DomainCmd\Application\UpdateApplicationCompletion::class,
            [
                'id' => self::APP_ID,
                'section' => 'businessDetails',
                'data' => [
                    'hasChanged' => $expectHasChanged,
                ]
            ],
            (new DomainCmd\Result())
                ->addMessage('Section updated')
        );

        //  call
        $actual = $this->sut->updateApplicationCompetition(self::APP_ID, $expectHasChanged);

        static::assertInstanceOf(DomainCmd\Result::class, $actual);
        static::assertEquals(
            [
                'id' => [],
                'messages' => [
                    'Section updated',
                ],
            ],
            $actual->toArray()
        );
    }
}
