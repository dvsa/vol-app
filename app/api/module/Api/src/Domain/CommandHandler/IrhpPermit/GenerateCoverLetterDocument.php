<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\IrhpPermit;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\Exception\RuntimeException;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermit as IrhpPermitEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitApplication as IrhpPermitApplicationEntity;
use Dvsa\Olcs\Api\Entity\Permits\IrhpPermitType as IrhpPermitTypeEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Api\Entity\System\SubCategory as SubCategoryEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Doctrine\ORM\Query;

/**
 * Generate Cover Letter Document for IRHP Permit
 */
final class GenerateCoverLetterDocument extends AbstractCommandHandler
{
    protected $repoServiceName = 'IrhpPermit';

    /**
     * @var array
     */
    private $templates = [
        IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT
            => DocumentEntity::IRHP_PERMIT_ECMT_COVER_LETTER,
        IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT_SHORT_TERM
            => DocumentEntity::IRHP_PERMIT_SHORT_TERM_ECMT_COVER_LETTER,
        IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_ECMT_REMOVAL
            => DocumentEntity::IRHP_PERMIT_ECMT_REMOVAL_COVERING_LETTER,
        IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_BILATERAL
            => DocumentEntity::IRHP_PERMIT_ANN_BILAT_COVERING_LETTER,
        IrhpPermitTypeEntity::IRHP_PERMIT_TYPE_ID_MULTILATERAL
            => DocumentEntity::IRHP_PERMIT_ANN_MULTILAT_COVERING_LETTER,
    ];

    /**
     * Handle command
     *
     * @param CommandInterface $command Command
     *
     * @return Result
     * @throws RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /** @var IrhpPermitEntity $irhpPermit */
        $irhpPermit = $this->getRepo()->fetchById($command->getIrhpPermit(), Query::HYDRATE_OBJECT);
        $irhpPermitApplication = $irhpPermit->getIrhpPermitApplication();

        // get document template
        $template = $this->getTemplate($irhpPermitApplication);

        $description = sprintf(
            '%s %d',
            strtoupper(str_replace('_', ' ', $template)),
            $irhpPermit->getPermitNumber()
        );

        $irhpApplication = $irhpPermitApplication->getIrhpApplication();
        $licence = $irhpApplication->getLicence();

        $document = $this->handleSideEffect(
            GenerateAndStore::create(
                [
                    'template' => $template,
                    'query' => $this->getDocumentQuery($irhpPermit, $licence),
                    'knownValues' => [],
                    'irhpApplication' => $irhpApplication->getId(),
                    'licence' => $licence->getId(),
                    'description' => $description,
                    'category' => CategoryEntity::CATEGORY_PERMITS,
                    'subCategory' => SubCategoryEntity::DOC_SUB_CATEGORY_PERMIT_COVERING_LETTER,
                    'isExternal' => false,
                    'isScan' => false
                ]
            )
        );

        $this->result->addId('coveringLetter', $document->getId('document'), true);
        $this->result->addMessage($description . ' RTF created and stored');

        return $this->result;
    }

    /**
     * Get template
     *
     * @param IrhpPermitApplicationEntity $irhpPermitApplication IRHP Permit Application
     *
     * @return string
     * @throws RuntimeException
     */
    private function getTemplate(IrhpPermitApplicationEntity $irhpPermitApplication)
    {
        $irhpPermitTypeId = $irhpPermitApplication->getIrhpPermitWindow()->getIrhpPermitStock()
            ->getIrhpPermitType()->getId();

        if (!isset($this->templates[$irhpPermitTypeId])) {
            throw new RuntimeException(
                sprintf(
                    'Cover letter template not defined for IRHP Permit Type (id: %s)',
                    $irhpPermitTypeId
                )
            );
        }

        return $this->templates[$irhpPermitTypeId];
    }

    /**
     * Get document query
     *
     * @param IrhpPermitEntity $irhpPermit IRHP Permit
     * @param LicenceEntity    $licence    Licence
     *
     * @return array
     */
    private function getDocumentQuery(IrhpPermitEntity $irhpPermit, LicenceEntity $licence)
    {
        return [
            'licence' => $licence->getId(),
            'irhpPermit' => $irhpPermit->getId(),
        ];
    }
}
