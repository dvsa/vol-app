<?php

declare(strict_types=1);

/**
 * Print IRFO PSV Auth Checklist Test
 */

namespace Dvsa\OlcsTest\Api\Domain\CommandHandler\Irfo;

use Mockery as m;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\Irfo\PrintIrfoPsvAuthChecklist as Sut;
use Dvsa\Olcs\Api\Domain\Repository\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as IrfoPsvAuthEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType as IrfoPsvAuthTypeEntity;
use Dvsa\Olcs\Api\Entity\Organisation\Organisation as OrganisationEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Command\Irfo\PrintIrfoPsvAuthChecklist as Cmd;
use Dvsa\OlcsTest\Api\Domain\CommandHandler\AbstractCommandHandlerTestCase;

/**
 * Print IRFO PSV Auth Checklist Test
 */
class PrintIrfoPsvAuthChecklistTest extends AbstractCommandHandlerTestCase
{
    public function setUp(): void
    {
        $this->sut = new Sut();
        $this->mockRepo('IrfoPsvAuth', IrfoPsvAuth::class);

        parent::setUp();
    }

    /**
     *
     * @param int $irfoFeeTypeId
     * @param string $expectedTemplate
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('handleCommandProvider')]
    public function testHandleCommand(mixed $irfoFeeTypeId, mixed $expectedTemplate): void
    {
        $data = [
            'ids' => array_fill(0, Sut::MAX_IDS_COUNT, 'id')
        ];

        $command = Cmd::create($data);

        $orgId = 101;
        $org = m::mock(OrganisationEntity::class)->makePartial();
        $org->setId($orgId);

        /** @var IrfoPsvAuthEntity $irfoPsvAuth */
        $irfoPsvAuth1 = m::mock(IrfoPsvAuthEntity::class)->makePartial();
        $irfoPsvAuth1->setId(1001);
        $irfoPsvAuth1->setOrganisation($org);
        $irfoPsvAuth1
            ->shouldReceive('getIrfoPsvAuthType->getIrfoFeeType->getId')
            ->once()
            ->andReturn($irfoFeeTypeId);

        $this->repoMap['IrfoPsvAuth']->shouldReceive('fetchByIds')
            ->once()
            ->with($data['ids'])
            ->andReturn([$irfoPsvAuth1]);

        $docId1 = 10333;
        $generateResult1 = new Result();
        $generateResult1->addId('document', $docId1);

        $this->expectedSideEffect(
            GenerateAndStore::class,
            [
                'template' => 'IRFO_Checklist_Renewal_letter',
                'query' => [
                    'irfoPsvAuth' => 1001
                ],
                'knownValues' => [],
                'description' => 'IRFO PSV Auth Checklist Renewal letter (1001)',
                'irfoOrganisation' => $orgId,
                'category' => CategoryEntity::CATEGORY_IRFO,
                'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_IRFO_CONTINUATIONS_AND_RENEWALS,
                'isExternal' => false,
                'isScan' => false
            ],
            $generateResult1
        );

        $docQueueResult1 = new Result();
        $docQueueResult1->addMessage('Document queued: ' . $docId1);
        $this->expectedSideEffect(
            EnqueueFileCommand::class,
            [
                'documentId' => $docId1,
                'jobName' => 'IRFO PSV Auth Checklist Renewal letter (1001)'
            ],
            $docQueueResult1
        );

        $docId2 = 20333;
        $generateResult2 = new Result();
        $generateResult2->addId('document', $docId2);

        $this->expectedSideEffect(
            GenerateAndStore::class,
            [
                'template' => $expectedTemplate,
                'query' => [
                    'irfoPsvAuth' => 1001,
                    'organisation' => $orgId
                ],
                'knownValues' => [],
                'description' => 'IRFO PSV Auth Checklist Application letter (1001)',
                'irfoOrganisation' => $orgId,
                'category' => CategoryEntity::CATEGORY_IRFO,
                'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_IRFO_CONTINUATIONS_AND_RENEWALS,
                'isExternal' => false,
                'isScan' => false
            ],
            $generateResult2
        );

        $docQueueResult2 = new Result();
        $docQueueResult2->addMessage('Document queued: ' . $docId2);
        $this->expectedSideEffect(
            EnqueueFileCommand::class,
            [
                'documentId' => $docId2,
                'jobName' => 'IRFO PSV Auth Checklist Application letter (1001)'
            ],
            $docQueueResult2
        );

        $result = $this->sut->handleCommand($command);

        $this->assertInstanceOf(Result::class, $result);
    }

    /**
     * @return array
     */
    public static function handleCommandProvider(): array
    {
        return [
            [
                IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_EU_REG_17,
                'IRFO_app_eu_regular_service'
            ],
            [
                IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_EU_REG_19A,
                'IRFO_app_eu_regular_service'
            ],
            [
                IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_NON_EU_OCCASIONAL_19,
                'IRFO_app_non_eu_service'
            ],
            [
                IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_NON_EU_REG_18,
                'IRFO_app_non_eu_service'
            ],
            [
                IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_NON_EU_REG_19,
                'IRFO_app_non_eu_service'
            ],
            [
                IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_SHUTTLE_OPERATOR_20,
                'IRFO_app_non_eu_service'
            ],
            [
                IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_OWN_AC_21,
                'IRFO_app_non_eu_service'
            ],
        ];
    }

    public function testHandleCommandWithMaxIdsCountExceeded(): void
    {
        $this->expectException(\Dvsa\Olcs\Api\Domain\Exception\ValidationException::class);

        $data = [
            'ids' => array_fill(0, Sut::MAX_IDS_COUNT + 1, 'id')
        ];

        $command = Cmd::create($data);

        $this->sut->handleCommand($command);
    }
}
