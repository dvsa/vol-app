<?php

namespace Dvsa\Olcs\Api\Domain\CommandHandler\ContinuationDetail;

use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Entity\Licence\ContinuationDetail as ContinuationDetailEntity;
use Dvsa\Olcs\Api\Entity\System\Category as CategoryEntity;
use Dvsa\Olcs\Transfer\Command\CommandInterface;

/**
 * Process continuation detail - generate reminder letter
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class ProcessReminder extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'ContinuationDetail';

    protected $extraRepos = ['Document'];

    public function handleCommand(CommandInterface $command)
    {
        $continuationDetail = $this->getRepo()->fetchWithLicence($command->getId());

        $result = new Result();
        $result->merge($this->generateDocument($continuationDetail, $command->getUser()));
        $result->addMessage('Continuation checklist reminder letter generated');

        return $result;
    }

    /**
     * @return Result
     */
    protected function generateDocument(ContinuationDetailEntity $continuationDetail, $userId)
    {
        $template = $this->getTemplateName($continuationDetail);
        $licence = $continuationDetail->getLicence();

        $data = [
            'template' => $template,
            'query' => [
                'licence' => $licence->getId(),
                'user' => $userId
            ],
            'description' => 'Checklist reminder',
            'licence' => $continuationDetail->getLicence()->getId(),
            'category' => CategoryEntity::CATEGORY_LICENSING,
            'subCategory' => CategoryEntity::DOC_SUB_CATEGORY_CONTINUATIONS_AND_RENEWALS_LICENCE,
            'isExternal'  => false,
            'isScan' => false,
            'dispatch' => true
        ];

        return $this->handleSideEffect(GenerateAndStore::create($data));
    }

    protected function getTemplateName($continuationDetail)
    {
        $licence = $continuationDetail->getLicence();
        $template = 'LIC_CONTD_NO_CHECKLIST_';

        if ($licence->isGoods()) {
            $template .= 'GV';
        } else {
            $template .= 'PSV';
        }

        return $template;
    }
}
