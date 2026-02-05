<?php

/**
 * Generate a publication
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\Repository\PublicationLink;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Transfer\Command\Publication\Generate as GenerateCommand;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\RefData;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore as GenerateDocCommand;
use Dvsa\Olcs\Api\Domain\Command\Publication\CreateNextPublication as CreateNextPublicationCmd;

/**
 * Generate a publication
 */
final class Generate extends AbstractCommandHandler implements TransactionedInterface
{
    public const string DOCUMENT_DESCRIPTION = '%s %d generated';

    protected $repoServiceName = 'Publication';
    protected $extraRepos = ['PublicationLink'];

    /**
     * Generates a publication
     *
     * @param CommandInterface $command command to generate publication
     *
     * @return Result
     *
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var PublicationEntity $publication
         * @var GenerateCommand $command
         */
        $publication = $this->getRepo()->fetchUsingId($command);

        /** @var RefData $generatedPubStatus */
        $generatedPubStatus = $this->getRepo()->getRefdataReference(PublicationEntity::PUB_GENERATED_STATUS);

        /** @var PublicationLink $publicationLinks */
        $publicationLinks = $this->getRepo('PublicationLink');
        $ineligibleLinks = $publicationLinks->fetchIneligiblePublicationLinks($publication);

        /** @var Result $result */
        $result = $this->handleSideEffect(CreateNextPublicationCmd::create(['id' => $publication->getId()]));

        $newPublicationId = $result->getId('created_publication');
        /**
         * @var PublicationEntity $publication
         * @var GenerateCommand $command
         */
        $newPublication = $this->getRepo()->fetchById($newPublicationId);

        /** @var \Dvsa\Olcs\Api\Entity\Publication\PublicationLink $ineligibleLink */
        foreach ($ineligibleLinks as $ineligibleLink) {
            $ineligibleLink->setPublication($newPublication);
            $publicationLinks->save($ineligibleLink);
        }

        $result->merge(
            $this->handleSideEffect($this->getGenerateDocCommand($publication))
        );

        /** @var DocumentEntity $document */
        $document = $this->getRepo()->getReference(DocumentEntity::class, $result->getId('document'));

        $publication->generate($document, $generatedPubStatus);
        $this->getRepo()->save($publication);

        $result->addId('generated_publication', $publication->getId());
        $result->addMessage('Publication was generated');

        return $result;
    }

    /**
     * Creates the command for generating the document
     *
     * @param PublicationEntity $publication publication doctrine entity
     *
     * @return GenerateDocCommand
     */
    private function getGenerateDocCommand(PublicationEntity $publication)
    {
        $description = sprintf(
            self::DOCUMENT_DESCRIPTION,
            $publication->getDocTemplate()->getDescription(),
            $publication->getPublicationNo()
        );

        $data = [
            'template' => $publication->getDocTemplate()->getDocument()->getIdentifier(),
            'query' => [
                'publicationId' => $publication->getId(),
                'pubType' => $publication->getPubType()
            ],
            'description'   => $description,
            'category'      => $publication->getDocTemplate()->getCategory()->getId(),
            'subCategory'   => $publication->getDocTemplate()->getSubCategory()->getId(),
            'isExternal'    => true,
        ];

        return GenerateDocCommand::create($data);
    }
}
