<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command as DomainCmd;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateCompanySubsidiary;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\Application\UpdateCompanySubsidiary
 */
class UpdateCompanySubsidiaryTest extends AbstractCommandHandlerTestCase
{
    public const ID = 666;
    public const APP_ID = 8888;
    public const LICENCE_ID = 7777;

    /** @var  UpdateCompanySubsidiary|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(UpdateCompanySubsidiary::class . '[update, updateApplicationCompetition]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('Application', Repository\Application::class);
        $this->mockRepo('CompanySubsidiary', Repository\CompanySubsidiary::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'application' => self::APP_ID,
            'name' => 'unit_Name',
            'companyNo' => '12345678',
        ];
        $command = TransferCmd\Application\CreateCompanySubsidiary::create($data);

        //  mock create result
        $result = (new Result())
            ->setFlag('hasChanged', true)
            ->addMessage('Company Subsidiary updated');

        $this->sut->shouldReceive('update')->once()->with($command)->andReturn($result);

        //  mock Application completion
        $this->sut->shouldReceive('updateApplicationCompetition')
            ->once()
            ->with(self::APP_ID, true)
            ->andReturn(
                (new Result())
                    ->addId('companySubsidiary', self::ID)
                    ->addMessage('Section updated')
            );

        //  call & check
        $actual = $this->sut->handleCommand($command);

        static::assertInstanceOf(Result::class, $actual);

        $expected = [
            'id' => [
                'companySubsidiary' => self::ID,
            ],
            'messages' => [
                'Company Subsidiary updated',
                'Section updated',
            ],
            'flags' => ['hasChanged' => 1]
         ];
        static::assertEquals($expected, $actual->toArray());
    }
}
