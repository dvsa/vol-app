<?php

declare(strict_types=1);

/**
 * Generate Irfo Gv Permit Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Doctrine\ORM\Query;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\GenerateIrfoGvPermit as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrfoGvPermit as IrfoGvPermitRepo;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoGvPermit as IrfoGvPermitEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Command\Irfo\GenerateIrfoGvPermit as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;
use Mockery as m;

/**
 * Generate Irfo Gv Permit Test
 */
class GenerateIrfoGvPermitTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('IrfoGvPermit', IrfoGvPermitRepo::class);

        parent::setUp();
    }

    /**
     * testHandleCommand
     */
    public function testHandleCommand(): void
    {
        $id = 99;
        $orgId = 101;
        $noOfCopies = 5;

        $command = Cmd::Create(
            [
                'id' => $id,
            ]
        );

        /** @var IrfoGvPermitEntity $irfoGvPermit */
        $irfoGvPermit = m::mock(IrfoGvPermitEntity::class);
        $irfoGvPermit->shouldReceive('isGeneratable')->andReturn(true);
        $irfoGvPermit->shouldReceive('getId')->andReturn($id);
        $irfoGvPermit->shouldReceive('getNoOfCopies')->andReturn($noOfCopies);
        $irfoGvPermit->shouldReceive('getOrganisation->getId')->andReturn($orgId);
        $irfoGvPermit->shouldReceive('getIrfoGvPermitType->getIrfoCountry->getDescription')->andReturn('irfo country');

        $this->repoMap['IrfoGvPermit']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($irfoGvPermit);

        $this->expectedSideEffect(
            GenerateAndStore::class,
            [
                'template' => 'IRFO_GV_irfo_country',
                'query' => [
                    'irfoGvPermit' => $id,
                    'organisation' => $orgId
                ],
                'knownValues' => [],
                'description' => 'IRFO GV Permit (99) x 5',
                'irfoOrganisation' => $orgId,
                'category' => CategoryEntity::CATEGORY_IRFO,
                'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_IRFO_CONTINUATIONS_AND_RENEWALS,
                'isExternal' => false,
                'isScan' => false
            ],
            new Result()
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    public function testHandleCommandThrowsException(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\BadRequestException::class);

        $command = Cmd::Create(
            [
                'id' => 99,
            ]
        );

        /** @var IrfoGvPermitEntity $irfoGvPermit */
        $irfoGvPermit = m::mock(IrfoGvPermitEntity::class);
        $irfoGvPermit->shouldReceive('isGeneratable')->andReturn(false);

        $this->repoMap['IrfoGvPermit']->shouldReceive('fetchUsingId')
            ->with($command, Query::HYDRATE_OBJECT)
            ->andReturn($irfoGvPermit);

        $this->sut->handleCommand($command);
    }
}
