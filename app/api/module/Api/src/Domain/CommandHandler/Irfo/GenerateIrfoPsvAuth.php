<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Irfo;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Exception;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuth as IrfoPsvAuthEntity;
use Dvsa\Olcs\Api\Entity\Irfo\IrfoPsvAuthType as IrfoPsvAuthTypeEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Generate IrfoPsvAuth
 */
final class GenerateIrfoPsvAuth extends AbstractCommandHandler implements TransactionedInterface
{
    use IrfoPsvAuthUpdateTrait;

    protected $repoServiceName = 'IrfoPsvAuth';

    protected $extraRepos = ['Fee'];

    /**
     * Handle Generate command
     *
     * @param CommandInterface $command
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        // common IRFO PSV Auth update
        $irfoPsvAuth = $this->updateIrfoPsvAuth($command);

        $irfoPsvAuth->generate(
            $this->getRepo('Fee')->fetchFeesByIrfoPsvAuthId($irfoPsvAuth->getId(), true)
        );

        $this->getRepo()->save($irfoPsvAuth);

        // generate related documents
        $templates = $this->getTemplates($irfoPsvAuth);

        if (empty($templates)) {
            throw new Exception\BadRequestException(
                'No template found for given IRFO PSV Auth Type: '
                . $irfoPsvAuth->getIrfoPsvAuthType()->getIrfoFeeType()->getId()
            );
        }

        $description = sprintf(
            'IRFO PSV Authorisation (%d) x %d',
            $irfoPsvAuth->getId(),
            $command->getCopiesRequiredTotal()
        );

        foreach ($templates as $template) {
            // generate document
            $this->handleSideEffect(
                GenerateAndStore::create(
                    [
                        'template' => $template,
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
        }

        $result = new Result();
        $result->addId('irfoPsvAuth', $irfoPsvAuth->getId());
        $result->addMessage('IRFO PSV Auth generated successfully');

        return $result;
    }

    /**
     * Get templates
     *
     *
     * @return array
     */
    private function getTemplates(IrfoPsvAuthEntity $irfoPsvAuth)
    {
        $templates = [];

        $templates = match ($irfoPsvAuth->getIrfoPsvAuthType()->getIrfoFeeType()->getId()) {
            IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_EU_REG_17, IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_EU_REG_19A => ['IRFO_eu_auth_pink_GV280'],
            IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_NON_EU_REG_18 => [
                'IRFO_uk_green_authorisation_INT_P17',
                'IRFO_non_eu_blue_authorisation_to_foreign_partner_INT_P18'
            ],
            IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_NON_EU_REG_19 => ['IRFO_non_eu_blue_authorisation_foreign_operator_no_partner_INT_P18A'],
            IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_NON_EU_OCCASIONAL_19, IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_SHUTTLE_OPERATOR_20 => ['IRFO_eu_auth_pink_special_regular_GV280'],
            IrfoPsvAuthTypeEntity::IRFO_FEE_TYPE_OWN_AC_21 => ['IRFO_own_acc'],
            default => $templates,
        };

        return $templates;
    }
}
