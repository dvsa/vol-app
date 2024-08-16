<?php

/**
 * Generate Batch
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\CommunityLic;

use Dvsa\Olcs\Api\Domain\Command\CommunityLic\GenerateBatch as GenerateBatchCmd;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Entity;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Command\PrintScheduler\Enqueue as EnqueueFileCommand;
use Dvsa\Olcs\Api\Domain\Repository\SystemParameter as SystemParameterRepo;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\SystemParameter as SystemParameterEntity;
use Olcs\Logging\Log\Logger;

/**
 * Generate Batch
 *
 * @author Alex Peshkov <alex.peshkov@valtech.co.uk>
 */
final class GenerateBatch extends AbstractCommandHandler implements TransactionedInterface
{
    protected $repoServiceName = 'CommunityLic';

    protected $extraRepos = ['Licence', 'Application', 'SystemParameter'];

    public function handleCommand(CommandInterface $command)
    {
        /** @var GenerateBatchCmd $command */
        $isBatchReprint = $command->getIsBatchReprint();

        /**
         * @NOTE This check allows us to pass Application whenever possible, but otherwise Licence should be sufficient
         */
        if ($command->getIdentifier() !== null) {
            /** @var Entity\Application\Application $application */
            $application = $this->getRepo('Application')->fetchById($command->getIdentifier());
            $licence = $application->getLicence();
            $identifier = $application->getId();
            $template = $this->getTemplateForEntity($application, $isBatchReprint);
            $subCategory = $this->getDocumentSubCategory($application);
        } else {
            $licenceId = $command->getLicence();
            $licence = $this->getRepo('Licence')->fetchById($licenceId);
            $template = $this->getTemplateForEntity($licence, $isBatchReprint);
            $subCategory = $this->getDocumentSubCategory($licence);
            $identifier = null;
        }

        $communityLicenceIds = $command->getCommunityLicenceIds();
        Logger::debug(
            'Generating community licences: ' . implode(', ', $communityLicenceIds) . ' with template: ' . $template
        );

        foreach ($communityLicenceIds as $id) {
            $query = [
                'licence' => $licence->getId(),
                'communityLic' => $id,
                'application' => $identifier
            ];

            $docId = $this->generateDocument($template, $query, $subCategory);

            $printQueue = EnqueueFileCommand::create(
                [
                    'documentId' => $docId,
                    'jobName' => 'Community Licence',
                    'user' => $command->getUser(),
                ]
            );

            $this->result->merge($this->handleSideEffect($printQueue));

            $this->result->addMessage("Community Licence {$id} processed");
        }

        return $this->result;
    }

    protected function generateDocument($template, $query, $subCategory)
    {
        $dtoData = [
            'template' => $template,
            'query' => $query,
            'description' => 'Community licence',
            'category' => Entity\System\Category::CATEGORY_LICENSING,
            'subCategory' => $subCategory,
            'isExternal' => false,
            'isScan' => false
        ];

        $result = $this->handleSideEffect(GenerateAndStore::create($dtoData));

        $this->result->merge($result);

        return $result->getId('document');
    }

    /**
     * Decide what system parameter to use depending on whether this is a reprint
     *
     * @param bool $isBatchReprint whether this is a reprint
     *
     * @return string
     */
    private function getSystemParameterBasedOnReprint(bool $isBatchReprint): string
    {
        if ($isBatchReprint) {
            return SystemParameterEntity::DISABLE_UK_COMMUNITY_LIC_REPRINT;
        }

        return SystemParameterEntity::DISABLE_UK_COMMUNITY_LIC_OFFICE;
    }

    /**
     * Replacement getTemplateForEntity method - now decides between EU and UK licence based on system param
     *
     * @param Entity\Application\Application|Entity\Licence\Licence $entity licence or application
     * @param bool isBatchReprint whether this is a batch reprint
     *
     * @return string
     */
    private function getTemplateForEntity($entity, bool $isBatchReprint): string
    {
        $checkedSystemParam = $this->getSystemParameterBasedOnReprint($isBatchReprint);

        /** @var SystemParameterRepo $repo */
        $repo = $this->getRepo('SystemParameter');
        $ukCommunityLicenceDisabled = $repo->fetchValue($checkedSystemParam);

        if ($ukCommunityLicenceDisabled) {
            return $this->getTemplateForEuCommunityLicence($entity);
        }

        return $this->getTemplateForUkCommunityLicence($entity);
    }

    /**
     * Get the template for the EU community licence
     *
     * @param Entity\Application\Application|Entity\Licence\Licence $entity
     *
     * @return string
     */
    private function getTemplateForEuCommunityLicence($entity): string
    {
        if ($entity->isPsv()) {
            $prefix = 'PSV';
        } elseif ($entity->getNiFlag() === 'Y') {
            $prefix = 'GV_NI';
        } else {
            $prefix = 'GV_GB';
        }

        return $prefix . '_European_Community_Licence';
    }

    /**
     * Get the template for the UK community licence
     *
     * @param Entity\Application\Application|Entity\Licence\Licence $entity
     *
     * @return string
     */
    private function getTemplateForUkCommunityLicence($entity): string
    {
        if ($entity->isPsv()) {
            return DocumentEntity::GV_UK_COMMUNITY_LICENCE_PSV;
        }

        if ($entity->getNiFlag() === 'Y') {
            if ($entity->isLgv()) {
                return DocumentEntity::LGV_UK_COMMUNITY_LICENCE_NI;
            }
            return DocumentEntity::GV_UK_COMMUNITY_LICENCE_NI;
        }

        if ($entity->isLgv()) {
            return DocumentEntity::LGV_UK_COMMUNITY_LICENCE_GB;
        }

        return DocumentEntity::GV_UK_COMMUNITY_LICENCE_GB;
    }

    /**
     * Get the sub category for the document
     *
     * @param Entity\Application\Application|Entity\Licence\Licence $entity
     *
     * @return int
     */
    private function getDocumentSubCategory($entity): int
    {
        if ($entity->isPsv()) {
            return Entity\System\SubCategory::DOC_SUB_CATEGORY_PSV_CERTIFIED_COPY;
        }

        return Entity\System\SubCategory::DOC_SUB_CATEGORY_COMMUNITY_LICENCE;
    }
}
