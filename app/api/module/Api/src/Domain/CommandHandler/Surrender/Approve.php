<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Surrender;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\Exception\ForbiddenException;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\Licence\Licence;
use Dvsa\Olcs\Api\Entity\Surrender;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Licence\SurrenderLicence;
use Dvsa\Olcs\Transfer\Command\Surrender\Update as UpdateSurrender;

class Approve extends AbstractSurrenderCommandHandler
{
    protected $extraRepos = ['Licence'];

    /** @var Licence */
    protected $licenceEntity;

    /**
     * @param CommandInterface $command
     *
     * @return Result
     * @throws RuntimeException
     * @throws \Exception
     */
    public function handleCommand(CommandInterface $command)
    {
        $this->licenceEntity = $this->getRepo('Licence')->fetchById($command->getId());

        $this->hasEcmsAndSignatureBeenChecked($command->getId());

        $updateSurrenderResult = $this->handleSideEffect(UpdateSurrender::create(
            [
                'id' => $command->getId(),
                'status' => Surrender::SURRENDER_STATUS_APPROVED
            ]
        ));
        $this->result->addMessage($updateSurrenderResult);

        $surrenderLicenceResult = $this->handleSideEffect(SurrenderLicence::create(
            [
                'id' => $command->getId(),
                'surrenderDate' => $command->getSurrenderDate(),
                'terminated' => false
            ]
        ));

        $this->result->addMessage($surrenderLicenceResult);

        $this->result->merge($this->generateDocumentAndSendNotificationEmail($command->getId()));

        return $this->result;
    }

    /**
     * Generate/store document and send notification email
     *
     *
     * @return Result
     */
    private function generateDocumentAndSendNotificationEmail(int $licId): Result
    {
        [$template, $description] = $this->returnTemplateAndDescription();

        $dtoData = [
            'template' => $template,
            'query' => [
                'licence' => $licId,
            ],
            'description' => $description,
            'licence' => $licId,
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::TASK_SUB_CATEGORY_APPLICATION_SURRENDER,
            'isExternal' => true,
            'isScan' => false,
            'dispatch' => true
        ];

        return $this->handleSideEffect(GenerateAndStore::create($dtoData));
    }

    /**
     * Return array containing template and description, or throw exception if licence type is not surrenderable
     *
     * @return array
     *
     * @throws ForbiddenException
     */
    private function returnTemplateAndDescription(): array
    {
        $goodsOrPsv = $this->licenceEntity->getGoodsOrPsv()->getId();
        $licType = $this->licenceEntity->getLicenceType()->getId();
        $isNi = $this->licenceEntity->isNi();

        if ($goodsOrPsv === Licence::LICENCE_CATEGORY_PSV && $this->isValidLicenceType($licType) && !$isNi) {
            return ['GB/SURRENDER_LETTER_TO_OPERATOR_PSV', 'PSV - Surrender actioned letter'];
        }

        if ($goodsOrPsv === Licence::LICENCE_CATEGORY_GOODS_VEHICLE && $this->isValidLicenceType($licType) && !$isNi) {
            return ['GB/SURRENDER_LETTER_TO_OPERATOR_GV_GB', 'GV - Surrender actioned letter'];
        }

        if ($goodsOrPsv === Licence::LICENCE_CATEGORY_GOODS_VEHICLE && $this->isValidLicenceType($licType) && $isNi) {
            return ['NI/SURRENDER_LETTER_TO_OPERATOR_GV_NI', 'GV - Surrender actioned letter (NI)'];
        }

        throw new ForbiddenException('Licence type not surrenderable');
    }

    /**
     * Whether the specified licence type is valid for surrenders
     *
     *
     * @return bool
     */
    private function isValidLicenceType(string $licType): bool
    {
        $validTypes = [
            Licence::LICENCE_TYPE_STANDARD_NATIONAL,
            Licence::LICENCE_TYPE_STANDARD_INTERNATIONAL,
            Licence::LICENCE_TYPE_RESTRICTED,
            Licence::LICENCE_TYPE_SPECIAL_RESTRICTED,
        ];

        return in_array($licType, $validTypes);
    }

    /**
     * Throw exception if ecms and signature checks not complete
     *
     *
     * @throws ForbiddenException
     */
    private function hasEcmsAndSignatureBeenChecked(int $licId): void
    {
        /** @var Surrender $surrender */
        $surrender = $this->getSurrender($licId);
        if (($surrender->getEcmsChecked() && $surrender->getSignatureChecked()) === false) {
            throw new ForbiddenException('The surrender has not been checked');
        }
    }
}
