<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateCompanySubsidiary;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

#[\PHPUnit\Framework\Attributes\CoversClass(\Dvsa\Olcs\Api\Domain\CommandHandler\Application\CreateCompanySubsidiary::class)]
final class CreateCompanySubsidiaryTest extends AbstractCommandHandlerTestCase
{
    public const int ID = 666;
    public const int APP_ID = 8888;
    public const int LICENCE_ID = 7777;

    /** @var  CreateCompanySubsidiary|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(CreateCompanySubsidiary::class . '[create, updateApplicationCompetition]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('CompanySubsidiary', Repository\CompanySubsidiary::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        /** @var m\MockInterface|Entity\Licence\Licence $mockLicEntity */
        $mockLicEntity = m::mock(Entity\Licence\Licence::class)
            ->shouldReceive('getId')->once()->andReturn(self::LICENCE_ID)
            ->getMock();

        /** @var Entity\Application\Application $mockAppEntity */
        $mockAppEntity = m::mock(Entity\Application\Application::class)
            ->shouldReceive('getLicence')->once()->andReturn($mockLicEntity)
            ->getMock();

        $data = [
            'application' => self::APP_ID,
            'name' => 'unit_Name',
            'companyNo' => '12345678',
        ];
        $command = TransferCmd\Application\CreateCompanySubsidiary::create($data);

        $this->repoMap['Application']->shouldReceive('fetchById')
            ->with(self::APP_ID)
            ->andReturn($mockAppEntity);

        //  mock create result
        $result = new Result()
            ->addId('companySubsidiary', self::ID)
            ->addMessage('Company Subsidiary created');

        $this->sut->shouldReceive('create')
            ->once()
            ->with($command, self::LICENCE_ID)
            ->andReturn($result);

        //  mock Application completion
        $this->sut->shouldReceive('updateApplicationCompetition')
            ->once()
            ->with(self::APP_ID, true)
            ->andReturn(
                new Result()
                    ->addId('companySubsidiary', self::ID)
                    ->addMessage('Section updated')
            );

        //  call & check
        $actual = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $actual);

        $expected = [
            'id' => [
                'companySubsidiary' => self::ID,
            ],
            'messages' => [
                'Company Subsidiary created',
                'Section updated',
            ]
        ];
        $this->assertEquals($expected, $actual->toArray());
    }
}
