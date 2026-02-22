<?php

/**
 * Print IRFO Psv Auth Checklist
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Print IRFO Psv Auth Checklist
 */
final class PrintIrfoPsvAuthChecklist extends AbstractCommandHandler implements TransactionedInterface
{
    public const int MAX_IDS_COUNT = 100;

    protected $repoServiceName = 'IrfoPsvAuth';

    /**
     * Handle the command
     *
     * @param CommandInterface $command
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $ids = $command->getIds();

        if (count($ids) > self::MAX_IDS_COUNT) {
            throw new Exception\ValidationException(
                ['Number of selected records must be less than or equal to ' . self::MAX_IDS_COUNT]
            );
        }

        $irfoPsvAuthList = $this->getRepo()->fetchByIds($ids);

        $result = new Result();

        foreach ($irfoPsvAuthList as $irfoPsvAuth) {
            $result->merge($this->printChecklist($irfoPsvAuth));
        }

        return $result;
    }

    /**
     * Print IRFO PSV Auth Checklist
     *
     *
     * @return Result
     */
    private function printChecklist(IrfoPsvAuth $irfoPsvAuth)
    {
        $result = new Result();

        // print renewal letter
        $result->merge($this->printRenewalLetter($irfoPsvAuth));

        // print application letter
        $result->merge($this->printApplicationLetter($irfoPsvAuth));

        return $result;
    }

    /**
     * Print Renewal Letter
     *
     *
     * @return Result
     */
    private function printRenewalLetter(IrfoPsvAuth $irfoPsvAuth)
    {
        $description = sprintf(
            'IRFO PSV Auth Checklist Renewal letter (%d)',
            $irfoPsvAuth->getId()
        );

        // generate document
        $result = $this->handleSideEffect(
            GenerateAndStore::create(
                [
                    'template' => 'IRFO_Checklist_Renewal_letter',
                    'query' => [
                        'irfoPsvAuth' => $irfoPsvAuth->getId()
                    ],
                    'knownValues' => [],
                    'description' => $description,
                    'irfoOrganisation' => $irfoPsvAuth->getOrganisation()->getId(),
                    'category' => CategoryEntity::CATEGORY_IRFO,
                    'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_IRFO_CONTINUATIONS_AND_RENEWALS,
                    'isExternal' => false,
                    'isScan' => false
                ]
            )
        );

        // add to the print queue
        return $this->handleSideEffect(
            EnqueueFileCommand::create(
                [
                    'documentId' => $result->getId('document'),
                    'jobName' => $description
                ]
            )
        );
    }

    /**
     * Print Application Letter
     *
     *
     * @return Result
     */
    private function printApplicationLetter(IrfoPsvAuth $irfoPsvAuth)
    {
        $printResult = new Result();

        $description = sprintf(
            'IRFO PSV Auth Checklist Application letter (%d)',
            $irfoPsvAuth->getId()
        );

        // generate document
        $result = $this->handleSideEffect(
            GenerateAndStore::create(
                [
                    'template' => $this->getApplicationLetterTemplate($irfoPsvAuth),
                    'query' => [
                        'irfoPsvAuth' => $irfoPsvAuth->getId(),
                        'organisation' => $irfoPsvAuth->getOrganisation()->getId(),
                    ],
                    'knownValues' => [],
                    'description' => $description,
                    'irfoOrganisation' => $irfoPsvAuth->getOrganisation()->getId(),
                    'category' => CategoryEntity::CATEGORY_IRFO,
                    'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_IRFO_CONTINUATIONS_AND_RENEWALS,
                    'isExternal' => false,
                    'isScan' => false
                ]
            )
        );

        // add to the print queue
        $printResult->merge(
            $this->handleSideEffect(
                EnqueueFileCommand::create(
                    [
                        'documentId' => $result->getId('document'),
                        'jobName' => $description
                    ]
                )
            )
        );

        return $printResult;
    }

    /**
     * Get Application Letter Template
     *
     *
     * @return string
     */
    private function getApplicationLetterTemplate(IrfoPsvAuth $irfoPsvAuth)
    {
        $template = match ($irfoPsvAuth->getIrfoPsvAuthType()->getIrfoFeeType()->getId()) {
            IrfoPsvAuthType::IRFO_FEE_TYPE_EU_REG_17, IrfoPsvAuthType::IRFO_FEE_TYPE_EU_REG_19A => 'IRFO_app_eu_regular_service',
            default => 'IRFO_app_non_eu_service',
        };

        return $template;
    }
}
