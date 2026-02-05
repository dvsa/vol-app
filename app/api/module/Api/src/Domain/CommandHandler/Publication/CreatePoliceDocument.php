<?php

/**
 * Creates the police version of a publication
 */

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Publication;

use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Entity\Publication\Publication as PublicationEntity;
use Dvsa\Olcs\Api\Domain\Command\Publication\CreateNextPublication as CreateNextPublicationCmd;
use Dvsa\Olcs\Api\Domain\Command\Document\GenerateAndStore as GenerateDocCommand;

/**
 * Creates the police version of a publication
 */
final class CreatePoliceDocument extends AbstractCommandHandler implements TransactionedInterface
{
    public const string DOCUMENT_DESCRIPTION = '%s %d police version';

    protected $repoServiceName = 'Publication';

    /**
     * handles command to create the police document
     *
     * @param CommandInterface $command command
     *
     * @return Result
     * @throws \Dvsa\Olcs\Api\Domain\Exception\RuntimeException
     */
    public function handleCommand(CommandInterface $command)
    {
        /**
         * @var PublicationEntity $publication
         * @var CreateNextPublicationCmd
         */
        $publication = $this->getRepo()->fetchUsingId($command);

        return $this->handleSideEffect($this->persistPoliceDoc($publication));
    }

    /**
     * Copies the non-police document, then adds the police data
     *
     * @param PublicationEntity $publication publication doctrine entity
     *
     * @return GenerateDocCommand
     */
    private function persistPoliceDoc(PublicationEntity $publication)
    {
        $description = sprintf(
            self::DOCUMENT_DESCRIPTION,
            $publication->getDocTemplate()->getDescription(),
            $publication->getPublicationNo()
        );

        $data = [
            'template' => $publication->getDocument()->getId(),
            'query' => [
                'id' => $publication->getId()
            ],
            'description'   => $description,
            'category'      => $publication->getDocTemplate()->getCategory()->getId(),
            'subCategory'   => $publication->getDocTemplate()->getSubCategory()->getId(),
            'isExternal'    => true,
        ];

        return GenerateDocCommand::create($data);
    }
}
