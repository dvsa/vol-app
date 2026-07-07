<?php

declare(strict_types=1);

namespace Dvsa\Olcs\Api\Domain\CommandHandler\Document;

use Dvsa\Olcs\Api\Domain\Command\Result;
use Dvsa\Olcs\Api\Domain\CommandHandler\AbstractCommandHandler;
use Dvsa\Olcs\Api\Domain\CommandHandler\TransactionedInterface;
use Dvsa\Olcs\Api\Domain\ToggleAwareTrait;
use Dvsa\Olcs\Api\Domain\ToggleRequiredInterface;
use Dvsa\Olcs\Api\Entity\Doc\Document as DocumentEntity;
use Dvsa\Olcs\Api\Entity\System\FeatureToggle;
use Dvsa\Olcs\DocumentShare\Data\Object\File;
use Dvsa\Olcs\DocumentShare\Service\DocumentStoreInterface;
use Dvsa\Olcs\Transfer\Command\CommandInterface;
use Psr\Container\ContainerInterface;

final class OverwriteContent extends AbstractCommandHandler implements
    TransactionedInterface,
    ToggleRequiredInterface
{
    use ToggleAwareTrait;

    protected $toggleConfig = [
        FeatureToggle::INTERNAL_WEBDAV,
    ];

    protected $repoServiceName = 'Document';

    private DocumentStoreInterface $contentStore;

    #[\Override]
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $this->contentStore = $container->get('ContentStore');
        return parent::__invoke($container, $requestedName, $options);
    }

    #[\Override]
    public function handleCommand(CommandInterface $command): Result
    {
        /** @var DocumentEntity $document */
        $document = $this->getRepo()->fetchById($command->getId());

        $identifier = $document->getIdentifier();

        $file = new File();
        $file->setContent($command->getContent());

        $response = $this->contentStore->update($identifier, $file);

        if (!$response->isSuccess()) {
            throw new \Dvsa\Olcs\Api\Domain\Exception\RuntimeException(
                'Failed to overwrite document content for identifier: ' . $identifier
            );
        }

        $result = new Result();
        $result->addId('document', $document->getId());
        $result->addMessage('Document content overwritten');
        return $result;
    }
}
