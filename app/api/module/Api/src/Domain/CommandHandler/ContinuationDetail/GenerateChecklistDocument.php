<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\Licence\Licence as LicenceEntity;
use Dvsa\Olcs\Api\Entity\System\Category;
use Dvsa\Olcs\Api\Entity\Doc\Document;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Generate a Continuation checklist document
 */
final class GenerateChecklistDocument extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    /**
     * Handle command
     *
     * @param CommandInterface $command Command DTO
     *
     * @return Result
     */
    public function handleCommand(CommandInterface $command)
    {
        $continuationDetail = $this->getRepo()->fetchUsingId($command);
        $this->result->merge(
            $this->generateDocument($continuationDetail, $command->getUser(), $command->getEnforcePrint())
        );

        return $this->result;
    }

    /**
     * Generate the continuation checklist document
     *
     * @param ContinuationDetailEntity $continuationDetail Continuation detail
     * @param int                      $user               User who initiated the process
     * @param bool                     $enforcePrint       Enforce printing the docuemnt
     *
     * @return Result
     */
    protected function generateDocument(ContinuationDetailEntity $continuationDetail, $user, $enforcePrint)
    {
        $template = $this->getTemplate($continuationDetail);

        $licence = $continuationDetail->getLicence();

        $dtoData = [
            'template' => $template,
            'query' => [
                'licence' => $licence->getId(),
                'goodsOrPsv' => $licence->getGoodsOrPsv()->getId(),
                'licenceType' => $licence->getLicenceType()->getId(),
                'niFlag' => $licence->getNiFlag(),
                'organisation' => $licence->getOrganisation()->getId(),
                'user' => $user
            ],
            'description' => 'Continuation checklist',
            'licence' => $continuationDetail->getLicence()->getId(),
            'category' => Category::CATEGORY_LICENSING,
            'subCategory' => Category::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'isExternal'  => false,
            'isScan' => false,
            'dispatch' => true,
            'isEnforcePrint' => ($enforcePrint) ? 'Y' : 'N',
        ];

        return $this->handleSideEffect(GenerateAndStore::create($dtoData));
    }

    /**
     * Get the template ID for the checklist document
     *
     * @param ContinuationDetailEntity $continuationDetail Continuation detail
     *
     * @return int
     */
    protected function getTemplate($continuationDetail)
    {
        /* @var $licence LicenceEntity */
        $licence = $continuationDetail->getLicence();

        if ($licence->isGoods()) {
            if ($licence->isLgv()) {
                return ($licence->getNiFlag() === 'N') ?
                    Document::GV_LGV_CONTINUATION_CHECKLIST :
                    Document::GV_LGV_CONTINUATION_CHECKLIST_NI;
            }

            return ($licence->getNiFlag() === 'N') ?
                Document::GV_CONTINUATION_CHECKLIST :
                Document::GV_CONTINUATION_CHECKLIST_NI;
        }

        return ($licence->getLicenceType()->getId() === LicenceEntity::LICENCE_TYPE_SPECIAL_RESTRICTED) ?
            Document::PSV_CONTINUATION_CHECKLIST_SR :
            Document::PSV_CONTINUATION_CHECKLIST;
    }
}
