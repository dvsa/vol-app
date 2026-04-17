<?php

declare(strict_types=1);

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Application;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Application\DeleteCompanySubsidiary;
use Dvsa\Olcs\Api\Domain\Repository;
use Dvsa\Olcs\Transfer\Command as TransferCmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * @covers Dvsa\Olcs\Api\Domain\CommandHandler\Application\DeleteCompanySubsidiary
 */
class DeleteCompanySubsidiaryTest extends AbstractCommandHandlerTestCase
{
    public const APP_ID = 9999;

    /** @var DeleteCompanySubsidiary|m\MockInterface */
    protected $sut;

    public function setUp(): void
    {
        $this->sut = m::mock(DeleteCompanySubsidiary::class . '[delete, updateApplicationCompetition]')
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $this->mockRepo('CompanySubsidiary', Repository\CompanySubsidiary::class);

        parent::setUp();
    }

    public function testHandleCommand(): void
    {
        $data = [
            'application' => self::APP_ID,
            'ids' => [1, 2, 3],
        ];
        $command = TransferCmd\Application\DeleteCompanySubsidiary::create($data);

        $this->sut
            ->shouldReceive('delete')
            ->once()
            ->with($command)
            ->andReturn(
                (new Result())
                    ->addMessage('Company Subsidiary Removed')
            )
            //
            ->shouldReceive('updateApplicationCompetition')
            ->once()
            ->with(self::APP_ID, true)
            ->andReturn(
                (new Result())
                    ->addMessage('Section updated')
            );

        //  call & check
        $actual = $this->sut->handleCommand($command);

        $expected = [
            'id' => [],
            'messages' => [
                'Company Subsidiary Removed',
                'Section updated',
            ]
        ];
        static::assertEquals($expected, $actual->toArray());
        static::assertInstanceOf(Result::class, $actual);
    }
}
